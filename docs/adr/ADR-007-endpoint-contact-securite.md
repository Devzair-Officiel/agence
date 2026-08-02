# ADR-007 — Sécurité de l'endpoint `POST /api/contact`

- Statut : accepté
- Date : 2026-08-02
- Décideurs : équipe Devzair
- Portée : Phase 6A. Ré-évaluer si un formulaire authentifié (Phase 7+) ou
  un back-office (Phase 8+) introduit une session.

## Contexte

L'endpoint public `POST /api/contact` reçoit des soumissions du formulaire
de contact du site. Il déclenche un envoi d'email vers l'équipe. Il est
donc directement exposé aux abus classiques :

- soumissions automatisées (bots) → spam de la boîte de réception ;
- attaques CSRF depuis un site tiers ;
- **email injection** via en-têtes contrôlés par l'attaquant ;
- énumération d'utilisateurs / fuite de PII via les logs ;
- déni de service applicatif (payload volumineux, rafale de requêtes) ;
- vol de réputation SPF/DKIM si l'expéditeur SMTP est piloté par le
  visiteur.

La Phase 6A n'implémente **pas** le formulaire Nuxt ni le widget Turnstile
côté navigateur. Ces décisions doivent néanmoins être prises côté serveur
pour que le front puisse s'y brancher plus tard sans redesign.

## Options étudiées

### CSRF

- **Option A — Cookie double-submit** : Nuxt émet un cookie CSRF, le front
  le duplique dans un en-tête, Symfony vérifie l'égalité. Coût : session
  Nuxt + cookie explicite, casse le SSR statique de `/`.
- **Option B — Stateless : Origin allowlist + Turnstile + rate limit**.
  Aucun cookie ni session ; la protection repose sur la combinaison
  Origin, CAPTCHA serveur et rate limit par IP.

### Anti-bot

- Honeypot seul : trivial à contourner par un bot spécialisé.
- CAPTCHA visible (reCAPTCHA v2, hCaptcha) : friction visiteur importante,
  problèmes d'accessibilité.
- **Cloudflare Turnstile** : invisible sur la majorité du trafic,
  vérification serveur obligatoire, meilleur compromis UX / sécurité.

### Rate limit

- Middleware Nginx / Caddy : simple mais peu contextuel (pas de
  distinction endpoint).
- Symfony RateLimiter (token bucket) : contexte applicatif, `Retry-After`
  natif, testable.

### Logging

- Loguer la payload → non conforme (PII sensible : email, message,
  téléphone).
- Loguer uniquement l'événement + un `request_id` corrélable.

### Mailer

- `From` = email visiteur → viole SPF/DKIM du domaine Devzair.
- `From` piloté par l'app + `Reply-To` = visiteur → conforme et pratique.

## Décision

### CSRF — Option B (stateless)

- Aucun cookie ni session émis par l'endpoint.
- Vérification stricte de l'en-tête `Origin` contre une liste blanche
  (`CONTACT_ORIGIN_ALLOWLIST`). Une requête sans `Origin` est rejetée
  (une soumission POST cross-origin légitime en émet toujours un).
- Combiné à Turnstile + rate limit, le triple contrôle rend l'attaque
  CSRF non exploitable (l'attaquant ne peut pas forger un token
  Turnstile valide côté navigateur victime, ni contrôler l'Origin).

### Anti-spam / anti-bot

1. **Honeypot** — champ `website` invisible. Rempli → réponse 202
   générique identique au succès, **aucun email envoyé**, aucune
   validation exécutée (économise CPU sur trafic manifestement bot).
2. **Cloudflare Turnstile** — vérification serveur obligatoire via
   `TurnstileVerifierInterface`. Implémentation `AlwaysAllow` en dev/test,
   `Cloudflare` en prod. Absence de secret alors que `TURNSTILE_ENABLED=true`
   → exception au boot (fail-closed).
3. **Rate limit** — Symfony RateLimiter, policy `token_bucket`, clé =
   IP réelle (via trusted proxies), défaut 5 requêtes / 10 minutes. Sur
   dépassement, retourner `429` + header `Retry-After`.

### Validation

- DTO `App\Contact\Dto\ContactRequest` avec contraintes Symfony
  Validator déclarées en attributs PHP (NotBlank, Email, Length, Regex,
  Choice, IsTrue). Aucun HTML n'est jamais interprété.
- Payload > 10 KB → 413. Empêche toute tentative de DoS applicatif via
  message géant.

### Mailer

- `From` = adresse app-controlled (`CONTACT_FROM_EMAIL`, ex.
  `no-reply@devzair.fr`). Jamais l'email visiteur.
- `Reply-To` = adresse du visiteur, pour que l'équipe puisse répondre en
  un clic.
- **Aucun HTML** dans le corps : texte brut uniquement, supprime la
  surface d'injection.
- `MAILER_DSN` par défaut = `null://null` : aucun email envoyé tant que la
  boîte réelle n'est pas branchée. En prod, la valeur passera via secret
  manager (jamais dans Git).
- Si `siteIndexable=true` mais `CONTACT_RECIPIENT` vide → l'endpoint
  refuse d'accepter le message (503) pour éviter d'acquitter une
  soumission qui serait silencieusement perdue.

### Logging

Le canal Monolog `contact` reçoit uniquement des événements structurés :

| Événement                        | Payload logguée                              |
|----------------------------------|-----------------------------------------------|
| `contact.origin_rejected`        | `request_id`                                  |
| `contact.payload_too_large`      | `request_id`, `size`                          |
| `contact.rate_limited`           | `request_id`                                  |
| `contact.invalid_json`           | `request_id`                                  |
| `contact.honeypot_triggered`     | `request_id`                                  |
| `contact.validation_failed`      | `request_id`, `fields` (noms uniquement)      |
| `contact.turnstile_rejected`     | `request_id`, `reason` (code court)           |
| `contact.submitted`              | `request_id`, `project_type`                  |
| `contact.unhandled_exception`    | `request_id`, `exception`, `message`, `status`|

**Jamais** loggués : `message`, `email` visiteur, `telephone`,
`turnstileToken`, `TURNSTILE_SECRET`. Un test PHPUnit
(`ContactLoggingTest`) échoue si l'une de ces valeurs apparaît dans les
logs sur le happy path.

### Corrélation

- `Request-Id` généré à chaque requête (UUID v7, `Symfony\Component\Uid`).
- Exposé dans la réponse JSON (`request_id`) **et** dans l'en-tête HTTP
  (`X-Request-Id`).
- Repris tel quel dans tous les logs.

### Réponses HTTP

| Cas                              | Statut | Corps `code`         |
|----------------------------------|-------:|----------------------|
| Succès                           | 200    | `-` (`status: accepted`) |
| Honeypot rempli                  | 202    | `-` (`status: accepted`, identique au succès) |
| Origin absente ou non autorisée  | 403    | `origin_not_allowed` |
| Turnstile refusé                 | 403    | `turnstile_rejected` |
| Validation échouée               | 400    | `validation_failed`  |
| JSON invalide                    | 400    | `invalid_json`       |
| Payload > 10 KB                  | 413    | `payload_too_large`  |
| Rate limit atteint               | 429    | `rate_limited` + `Retry-After` |
| Erreur serveur                   | 500    | `server_error`       |

Toutes les réponses portent `Cache-Control: no-store` et `X-Request-Id`.

## Raisons

- **KISS** : pas de session, pas de store partagé, pas de widget CSRF —
  moins de code = moins de bugs de sécurité.
- **Défense en profondeur** : Origin + Turnstile + rate limit + honeypot
  + validation + limitation payload = 6 filtres indépendants.
- **Auditabilité** : chaque événement porte un `request_id`, aucun PII
  n'apparaît, une trace peut être suivie d'un log à l'autre sans risquer
  une fuite.
- **Conformité** : les données personnelles du visiteur ne sont ni
  loggées ni persistées ; seul le mail sortant les transporte (et son
  contenu est sous le contrôle du destinataire).

## Conséquences

Positives :

- endpoint testable de bout en bout sans dépendance réseau
  (`InMemoryContactMessageSender`, `AlwaysAllowTurnstileVerifier`) ;
- pipeline CI reproductible et sans secret ;
- migration future vers un formulaire authentifié = ADR séparé.

Négatives / à surveiller :

- **Boot fail** si `TURNSTILE_ENABLED=true` sans secret ; documenté et
  couvert par un test factory.
- **Warning attendu** si `siteIndexable=true` mais Turnstile désactivé :
  configuration probablement erronée en prod. Prévu pour être hoisté en
  erreur bloquante lors de la mise en ligne réelle du formulaire.

## Références

- `docs/05-SECURITY-PRIVACY.md` §14 (formulaires, RGPD, logs).
- `docs/06-ARCHITECTURE-CODE.md` §16 (couches, DI, contrats).
- ADR-006 (runtime Symfony/Caddy).
