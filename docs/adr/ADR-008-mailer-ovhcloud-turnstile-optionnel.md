# ADR-008 — Transport mail OVHcloud, Turnstile facultatif, réponse HTTP 503

- Statut : accepté
- Date : 2026-08-02
- Décideurs : équipe Devzair
- Portée : Phase 6C. Complète ADR-007 (sécurité du pipeline) sur le volet
  transport SMTP et la stratégie d'anti-abus. Ré-évaluer si la charge du
  formulaire justifie un jour Turnstile permanent, ou si un provider
  transactionnel (SendGrid, Postmark) remplace OVHcloud.

## Contexte

Après ADR-007 et l'implémentation Phases 6A/6B, il reste trois questions
opérationnelles pour la mise en production :

1. **Choix du transport SMTP.** L'hébergement Devzair vit chez OVHcloud
   (mutualisé + Exchange Pro). Le mailer applicatif doit émettre depuis un
   compte contrôlé par nous — jamais depuis l'email visiteur — sans figer
   le nom du serveur dans le code (le domaine peut migrer sans redeploy).
2. **Turnstile facultatif.** L'ADR-007 rend Turnstile requis en prod. En
   pratique, avant l'ouverture publique du formulaire, on ne veut ni
   dépendre de Cloudflare ni charger un script tiers sur toutes les pages
   pour un formulaire encore rarement soumis. Il faut pouvoir désactiver
   Turnstile *proprement* — donc sans laisser une fausse impression de
   sécurité, et sans que le front n'invoque quand même l'API Cloudflare.
3. **Réponse en cas d'échec SMTP.** Aujourd'hui, un échec SMTP remonte en
   500 générique. L'utilisateur ne sait pas si son message est parti. Il
   faut un statut HTTP explicite et un code métier stable, sans qu'on
   renvoie 202/200 pour « faire joli » — un utilisateur trompé sur un
   envoi c'est pire qu'une erreur honnête.

## Options étudiées

### Transport SMTP

- **Nom de serveur codé en dur** dans `services.yaml` ou `.env` versionné :
  refusé — casse à chaque migration hébergeur, et fait apparaître des
  détails d'infra dans Git.
- **`MAILER_DSN` seule variable d'entrée** (`smtps://user:pass@host:port`,
  `smtp://user:pass@host:port`, `null://null` en dev). Choisie : le nom du
  serveur reste hors du code, la substitution en prod se fait via secret
  manager, et le DSN transporte transparence + authentification + port.

### Turnstile facultatif

- **Retirer Turnstile de la codebase** — refusé : on veut pouvoir
  l'activer sans redeploy applicatif, uniquement via variables d'env.
- **Un flag serveur uniquement** (`TURNSTILE_ENABLED`) — insuffisant : le
  front continuerait à charger `challenges.cloudflare.com/turnstile/v0/api.js`
  et à monter l'iframe, ce qui est visible dans l'inspecteur et charge un
  script tiers pour rien.
- **Deux flags alignés** : `TURNSTILE_ENABLED` (API) et
  `NUXT_PUBLIC_TURNSTILE_ENABLED` (front). Choisie : un seul de
  désactivé/activé côté serveur ou front produit un rejet systématique,
  ce qui est le fail-mode correct. Défaut : `false` des deux côtés.

### Réponse 503 vs 500 vs 202

- **500 générique** : l'utilisateur ne peut pas distinguer erreur
  temporaire vs bug applicatif ; le front ne peut pas afficher un message
  utile ; les ops confondent avec les crashs.
- **202 « on retente en background »** : refusé — le message est perdu si
  le retry échoue, l'utilisateur croit son envoi acté, aucun canal ne le
  prévient.
- **503 `temporary_error`** : choisi. Statut HTTP normalisé pour
  indisponibilité temporaire, code métier stable, contrat clair côté
  front : afficher un bandeau explicite et *ne pas* vider le formulaire.

## Décision

### Transport SMTP

- Une seule variable d'entrée : `MAILER_DSN`, alimentée par Symfony
  Mailer. Aucun nom de serveur, aucun mot de passe n'est présent en Git.
  Le DSN peut prendre les formes suivantes :
    - `null://null` (dev/test, défaut sûr) — aucun envoi ;
    - `smtps://USER:PASS@HOST:465` — TLS implicite, recommandé en prod ;
    - `smtp://USER:PASS@HOST:587` — STARTTLS, accepté hors prod.
- Le composant Mailer est utilisé tel quel : pas de wrapper, pas de queue
  applicative. L'envoi est synchrone dans la requête HTTP — la fenêtre
  totale (validation + Turnstile + SMTP) doit rester sous 10 s en p99. Si
  cette contrainte devient un problème, l'ADR sera révisé pour introduire
  un worker Messenger.
- `CONTACT_FROM_EMAIL` et `CONTACT_FROM_NAME` restent app-controlled
  (jamais l'email visiteur). `CONTACT_RECIPIENT` est **obligatoire** en
  prod : absent → l'envoi lève `ContactTemporarilyUnavailableException`
  qui devient un 503 `temporary_error` (jamais un 200/202 « acquitté »).

### Turnstile facultatif (deux flags alignés)

- `TURNSTILE_ENABLED` (API, défaut `false`) — pilote le choix de
  `TurnstileVerifierInterface` :
    - `false` → `AlwaysAllowTurnstileVerifier` (accepte tout, y compris
      `dev-noop`) ;
    - `true` sans secret → exception au boot (fail-closed, contrat déjà
      posé par ADR-007) ;
    - `true` avec secret → `CloudflareTurnstileVerifier`.
- `NUXT_PUBLIC_TURNSTILE_ENABLED` (front, défaut `false`) — pilote le
  comportement du `TurnstileWidget` :
    - `false` → aucun `<script src="challenges.cloudflare.com/…">`
      n'est injecté, aucun iframe n'est monté, le widget émet
      immédiatement le token `dev-noop`. Une bannière visuelle
      « Mode dev — protection anti-bot désactivée » signale l'état
      pour éviter toute confusion en prod ;
    - `true` → chargement `async/defer` du script Cloudflare, rendu du
      widget standard.
- **Règle d'exploitation :** les deux flags *doivent* être alignés. Une
  divergence produit soit du script chargé pour rien, soit un rejet
  systématique. La checklist prod (`docs/checklists/PRODUCTION-CONTACT.md`)
  liste cette vérification en premier.

### HTTP 503 `temporary_error`

- Toute exception `TransportExceptionInterface` levée par le Mailer est
  captée par `SymfonyContactMessageSender` et re-levée en
  `ContactTemporarilyUnavailableException` (marker de domaine, message
  d'origine chaîné pour le debug interne uniquement).
- Absence de `CONTACT_RECIPIENT` est traitée de la même façon : lever
  `ContactTemporarilyUnavailableException` avant même l'envoi.
- `SubmitContactMessage` capture cette exception, log un event métier
  `contact.mailer_unavailable` (canal `contact`, niveau warning, aucun
  PII), et renvoie `ContactSubmissionResult::temporaryError()`.
- `ContactSubmissionController` traduit ce résultat en :

  ```
  HTTP/1.1 503 Service Unavailable
  Content-Type: application/json
  Cache-Control: no-store
  X-Request-Id: <uuid v7>

  {
    "status": "error",
    "code": "temporary_error",
    "request_id": "<uuid v7>"
  }
  ```

- **Contrat côté front (`useContactForm` + `ContactForm.vue`)** :
    - le code `temporary_error` est mappé sur un bandeau global à texte
      verbatim :
      > « Le service est momentanément indisponible. Votre message n'a
      > pas été envoyé. Merci de réessayer plus tard. » ;
    - le titre du bandeau est « Service momentanément indisponible » ;
    - le `request_id` est affiché sous le message pour permettre à
      l'utilisateur de le citer si besoin ;
    - **les valeurs saisies sont conservées** (pas de reset après échec) ;
    - **aucun retry automatique** côté navigateur — l'utilisateur décide.

## Validation de configuration au boot / à la demande

- Un service pur `ContactConfigurationValidator` inspecte la configuration
  résolue (paramètres du container, aucune I/O) et retourne un rapport
  d'anomalies. Règles vérifiées :
    - `MAILER_DSN` non vide ; en prod, ni `null://null` ni `smtp://`
      (plaintext) ;
    - `CONTACT_RECIPIENT` présent et syntaxiquement valide ;
    - `CONTACT_FROM_EMAIL` présent, valide, et non-example en prod
      (avertissement) ;
    - `CONTACT_FROM_NAME` non vide ;
    - Turnstile activé ⇒ secret présent ;
    - Turnstile désactivé en prod ⇒ avertissement (rappel opérationnel) ;
    - `CONTACT_ORIGIN_ALLOWLIST` non vide ;
    - `TRUSTED_PROXIES` non vide (avertissement).
- Une commande CLI `bin/console app:contact:check` invoque le validateur
  et retourne `0` (succès) ou `1` (erreurs bloquantes). La sortie ne
  révèle **jamais** le DSN complet, le secret Turnstile, ni les adresses
  email en clair — safe à coller dans un ticket d'ops.

## Raisons

- **KISS** : une variable pour le DSN, un couple de flags aligné pour
  Turnstile, un code d'erreur explicite. Aucune queue, aucun worker,
  aucun cache.
- **Honnêteté envers l'utilisateur** : 503 + message clair > 202 muet.
- **Pas de dépendance tierce imposée** : Turnstile activable à la
  demande, aucun script chargé sinon.
- **Auditabilité** : le validateur + la commande permettent un check
  reproductible sans envoyer un email test, ce qui évite d'induire de
  faux positifs dans la boîte destinataire.

## Conséquences

Positives :

- diagnostic Ops rapide (`app:contact:check`) sans effet de bord ;
- passer/dépasser OVHcloud (SendGrid, Postmark…) = changer une seule
  variable ;
- désactiver Turnstile en prod le temps d'un post-mortem ne casse pas le
  formulaire ;
- un failure SMTP transitoire produit un message actionnable côté client
  au lieu d'une 500.

Négatives / à surveiller :

- envoi synchrone : un SMTP OVHcloud lent (>2 s) allonge la p95. Si les
  logs `contact.mailer_unavailable` deviennent réguliers, envisager un
  Messenger avec fallback disque.
- absence de retry serveur : un pic OVHcloud génère plusieurs 503
  visibles en clientèle. Acceptable en Phase 6 (formulaire à faible
  volume) ; à revoir si le trafic augmente.
- deux flags Turnstile à maintenir alignés : erreur humaine possible.
  Compensé par la checklist de mise en prod et par un test E2E qui vérifie
  l'absence de script Cloudflare quand `NUXT_PUBLIC_TURNSTILE_ENABLED=false`.

## Références

- ADR-007 (sécurité de l'endpoint) — cadre général.
- `docs/05-SECURITY-PRIVACY.md` §14 (formulaires, RGPD, logs).
- `docs/06-ARCHITECTURE-CODE.md` §16 (couches, DI, contrats).
- `docs/checklists/PRODUCTION-CONTACT.md` (mise en prod formulaire).
- `apps/api/src/Contact/Configuration/` (validateur, rapport, issue).
- `apps/api/src/Contact/Command/ContactCheckCommand.php`.
- `apps/api/src/Contact/Exception/ContactTemporarilyUnavailableException.php`.
