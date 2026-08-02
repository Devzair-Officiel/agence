# Sécurité et protection des données

> Sécurité applicative, secrets, formulaires, administration, sauvegardes, cookies et conformité.

## 14. Sécurité

### Référentiel

- OWASP Top 10:2025 comme socle de sensibilisation ;
- OWASP ASVS 5.0 :
  - niveau 1 minimum pour le site public ;
  - niveau 2 pour toute administration, authentification, API métier, espace client ou traitement sensible.

## 14.1 Modèle de menace initial

Identifier :

- données collectées ;
- rôles ;
- surfaces publiques ;
- formulaires ;
- administration ;
- stockage de fichiers ;
- services externes ;
- clés API ;
- pipeline de déploiement ;
- sauvegardes ;
- accès hébergeur ;
- scénarios d’abus ;
- impact d’une indisponibilité ou fuite.

Mettre à jour le modèle à chaque fonctionnalité importante.

## 14.2 Secrets et configuration

- aucun secret dans Git ;
- `.env.example` sans valeur sensible ;
- secrets distincts par environnement ;
- rotation possible ;
- droits minimaux ;
- suppression des comptes et clés inutilisés ;
- variables de production injectées par le gestionnaire de secrets ou l’hébergeur ;
- préproduction séparée ;
- désactivation du mode debug en production ;
- messages d’erreur publics non détaillés.

## 14.3 Dépendances et chaîne logicielle

- fichiers de verrouillage versionnés ;
- mises à jour contrôlées ;
- analyse automatique des dépendances ;
- revue des alertes ;
- provenance des paquets ;
- nombre de dépendances réduit ;
- interdiction des paquets abandonnés sans justification ;
- inventaire logiciel ou SBOM si l’architecture le justifie ;
- intégrité des artefacts de build ;
- branche principale protégée.

## 14.4 Entrées et sorties

- validation serveur par liste d’autorisation ;
- limites de taille et format ;
- requêtes paramétrées ;
- encodage selon le contexte ;
- échappement automatique conservé ;
- HTML utilisateur interdit sauf besoin démontré et sanitisation robuste ;
- protection contre injections SQL, commandes, modèles et en-têtes ;
- validation des URL externes et prévention SSRF ;
- aucune confiance accordée aux données client.

## 14.5 Authentification et administration

Si une administration existe :

- URL non considérée comme protection ;
- MFA pour les comptes privilégiés ;
- mots de passe stockés avec algorithme moderne fourni par le framework ;
- limitation des tentatives ;
- réinitialisation sécurisée ;
- vérification de l’adresse e-mail si nécessaire ;
- sessions rotatives ;
- cookies `Secure`, `HttpOnly`, `SameSite` adapté ;
- expiration et révocation ;
- réauthentification pour actions sensibles ;
- journalisation des connexions et actions administratives ;
- rôles et permissions explicites ;
- contrôle d’accès côté serveur sur chaque action.

## 14.6 CSRF, XSS et politiques navigateur

- protection CSRF pour les actions basées sur session ;
- aucune action d’écriture via GET ;
- CSP déployée progressivement, d’abord en report-only si nécessaire ;
- éviter `unsafe-inline` et `unsafe-eval` ;
- nonces ou hashes pour les scripts nécessaires ;
- politique de framing via `frame-ancestors` ;
- contrôle strict des sources de scripts, images, connexions et formulaires.

## 14.7 En-têtes de sécurité

Configurer et tester selon l’architecture :

- `Content-Security-Policy` ;
- `Strict-Transport-Security` après validation HTTPS complète ;
- `X-Content-Type-Options: nosniff` ;
- `Referrer-Policy` ;
- `Permissions-Policy` ;
- `frame-ancestors` dans CSP ;
- `X-Frame-Options` comme compatibilité si nécessaire ;
- type de contenu correct et UTF-8 ;
- suppression des en-têtes révélant inutilement la technologie.

La politique exacte doit être générée à partir des ressources réellement utilisées.

## 14.8 Téléversement de fichiers

À éviter dans le MVP si non indispensable. Si présent :

- extensions autorisées ;
- vérification de signature/type réel ;
- taille limitée ;
- nom généré ;
- stockage hors racine publique ou service dédié ;
- téléchargement via contrôleur ;
- contrôle d’accès ;
- analyse antivirus ou sandbox lorsque disponible ;
- suppression automatique selon rétention ;
- protection CSRF ;
- blocage des fichiers actifs ;
- aucune exécution dans le répertoire de stockage.

## 14.9 Formulaires et anti-abus

- limitation par IP et autres signaux avec prudence ;
- honeypot accessible ;
- temporisation ;
- validation de cohérence ;
- blocage des volumes anormaux ;
- CAPTCHA uniquement en dernier recours, avec solution accessible ;
- protection contre l’envoi massif d’e-mails ;
- destinataires imposés côté serveur ;
- aucun en-tête e-mail construit directement depuis une entrée utilisateur.

### Endpoint `POST /api/contact` (Phase 6A)

Politique consignée dans `docs/adr/ADR-007-endpoint-contact-securite.md`.
Résumé — six filtres indépendants :

1. **CSRF stateless (Option B)** : allowlist stricte de l’en-tête `Origin`
   (`CONTACT_ORIGIN_ALLOWLIST`). Aucune session, aucun cookie. Une requête
   sans `Origin` correspondant est rejetée en 403.
2. **Honeypot** : champ invisible `website`. S’il est rempli, l’endpoint
   répond 202 générique et **n’envoie aucun email**.
3. **Cloudflare Turnstile** : vérification serveur obligatoire quand
   `TURNSTILE_ENABLED=true`. Un secret manquant en mode activé fait
   échouer le boot (fail-closed).
4. **Rate limit** : Symfony RateLimiter (token bucket) par IP réelle
   (`CONTACT_RATE_LIMIT` / `CONTACT_RATE_INTERVAL`). Dépassement → 429
   avec `Retry-After`.
5. **Validation stricte** : DTO `App\Contact\Dto\ContactRequest` avec
   contraintes Symfony (email, longueurs, choix). Payload > 10 KB → 413.
6. **Mailer contrôlé** : `From` app (jamais le visiteur), `Reply-To` =
   visiteur, corps texte brut (pas d’HTML → pas d’injection). Défaut
   `MAILER_DSN=null://null` : aucun email tant que la boîte réelle n’est
   pas branchée.

### Transport mail et échec SMTP (Phase 6C)

Politique consignée dans `docs/adr/ADR-008-mailer-ovhcloud-turnstile-optionnel.md`.
Compléments opérationnels Phase 6C :

- **`MAILER_DSN` seule variable d’entrée du transport** : jamais de nom
  d’hôte SMTP dans le code. Formes admises : `null://null` (dev/test),
  `smtps://user:pass@host:465` (TLS implicite, recommandé prod),
  `smtp://user:pass@host:587` (STARTTLS, hors prod). Le secret vit dans
  le gestionnaire de secrets, jamais dans Git.
- **Turnstile facultatif, deux flags alignés** : `TURNSTILE_ENABLED` (API)
  et `NUXT_PUBLIC_TURNSTILE_ENABLED` (front) doivent être identiques.
  Divergence = rejet systématique côté API ou script Cloudflare chargé
  pour rien. Le défaut sûr est `false` des deux côtés — aucun script
  tiers n’est injecté, le widget émet le token `dev-noop` accepté par
  `AlwaysAllowTurnstileVerifier`. Une bannière visible « Mode dev »
  signale l’état pour éviter toute confusion.
- **Échec SMTP → HTTP 503 `temporary_error`** : toute
  `TransportExceptionInterface` remonte via
  `ContactTemporarilyUnavailableException` et devient un 503 stable. Le
  front affiche un bandeau verbatim « Le service est momentanément
  indisponible. Votre message n’a pas été envoyé. Merci de réessayer
  plus tard. » et **conserve les valeurs saisies**. Aucun 200/202
  « acquitté » n’est renvoyé sur échec. `CONTACT_RECIPIENT` absent est
  traité identiquement (503 avant même l’envoi).
- **Diagnostic reproductible** : `bin/console app:contact:check` invoque
  `ContactConfigurationValidator` (service pur, aucune I/O), retourne
  code `0` (succès) ou `1` (erreurs bloquantes). La sortie ne révèle
  jamais le DSN complet, le secret Turnstile ni les emails en clair —
  peut être collée dans un ticket d’ops sans précaution. La checklist
  `docs/checklists/PRODUCTION-CONTACT.md` détaille la séquence complète
  de mise en production OVHcloud.
- **Log `contact.mailer_unavailable`** : canal Monolog `contact`, niveau
  warning, aucun PII (mêmes garanties que le happy path — cf. §14.11).

## 14.10 Transport, hébergement et réseau

- HTTPS obligatoire ;
- protocoles et suites modernes gérés par l’hébergeur ;
- redirection HTTP vers HTTPS ;
- accès d’administration restreints ;
- base de données non exposée publiquement ;
- pare-feu et règles minimales ;
- CDN/WAF configuré sans bloquer les robots choisis ;
- protection DDoS adaptée au risque ;
- environnements isolés.

## 14.11 Journaux, alertes et incidents

Journaliser sans enregistrer inutilement les données personnelles :

- échecs d’authentification ;
- changements de privilèges ;
- actions administratives ;
- erreurs serveur ;
- anomalies de formulaire ;
- alertes de sécurité ;
- déploiements ;
- sauvegardes.

Règle appliquée en Phase 6A pour `/api/contact` (canal Monolog `contact`) :
chaque événement porte un `request_id` (UUID v7) exposé aussi dans la
réponse et l’en-tête `X-Request-Id`. **Aucun PII** n’est loggué (ni
message visiteur, ni email, ni téléphone, ni token Turnstile, ni secret).
Un test PHPUnit (`ContactLoggingTest`) échoue si ces valeurs apparaissent
dans les logs sur le happy path.

Prévoir :

- horodatage cohérent ;
- accès restreint ;
- durée de conservation ;
- alertes exploitables ;
- procédure d’incident ;
- contact responsable ;
- rotation des secrets ;
- restauration ;
- analyse post-incident.

## 14.12 Sauvegardes

- sauvegardes automatiques ;
- chiffrement ;
- emplacement séparé ;
- rétention définie ;
- contrôle des succès ;
- test réel de restauration ;
- documentation du RPO/RTO lorsque le service devient critique.

---

## 15. Protection des données et conformité

Ce référentiel n’est pas un avis juridique. Les textes finaux doivent être validés selon la structure juridique, l’hébergement, les prestataires et les traitements réels.

### Inventaire des traitements

Pour chaque collecte :

- finalité ;
- données ;
- base légale ;
- destinataires ;
- sous-traitants ;
- transfert hors EEE ;
- durée de conservation ;
- mesures de sécurité ;
- droits des personnes ;
- preuve de consentement si requise.

### Minimisation

- ne collecter que le nécessaire ;
- rendre facultatifs les champs non indispensables ;
- ne pas demander de données sensibles par formulaire ;
- supprimer ou anonymiser à échéance ;
- séparer prospection et réponse à une demande.

### Cookies et traceurs

- aucun traceur non essentiel avant consentement lorsqu’il est requis ;
- bouton accepter et refuser au même niveau ;
- choix par finalité ;
- preuve du choix ;
- retrait aussi simple que l’acceptation ;
- lien permanent de gestion des préférences ;
- politique mise à jour ;
- vérification des scripts tiers après chaque changement.

Pour une mesure d’audience présentée comme exemptée de consentement, vérifier que la solution et sa configuration satisfont réellement toutes les conditions de la CNIL.

### Formulaires

- information courte à proximité ;
- lien vers la politique complète ;
- pas de case obligatoire de consentement si la base légale n’est pas le consentement ;
- opt-in séparé, facultatif et non précoché pour la prospection ;
- mécanisme de traitement des demandes d’accès, rectification, opposition et suppression.

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
