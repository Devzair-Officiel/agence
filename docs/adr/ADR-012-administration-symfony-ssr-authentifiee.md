# ADR-012 — Administration Symfony SSR authentifiée (Phase 8C1)

- Statut : accepté
- Date : 2026-08-06
- Décideurs : équipe Devzair

## Contexte

Les phases 8A/8B ont livré le domaine éditorial en lecture publique
(persistance PostgreSQL, import Markdown CLI, API JSON conditionnelle,
SSR Nuxt + cache Nitro). L'administration éditoriale — création, mise à
jour, publication d'articles depuis un navigateur — reste à construire.
Avant toute écriture éditoriale HTTP, il faut poser un socle
d'authentification qui :

1. isole rigoureusement la surface authentifiée du site public
   (contenu, session, cookies, CSP) ;
2. donne un point unique de vérification d'identité, résistant aux
   attaques classiques (fixation de session, brute force,
   énumération d'email, XSS, clickjacking) ;
3. n'expose aucune surface JavaScript ou API distante superflue tant
   qu'il n'y a pas de cas d'usage éditorial ;
4. reste simple à administrer en Phase 8 (un seul conteneur `api`, pas
   de Redis, pas d'IdP externe).

Trois questions structurent l'ADR :

1. **Où rendre l'administration ?** Nuxt + API JSON dédiée, ou Symfony
   + Twig SSR ?
2. **Comment porter la preuve d'identité ?** Cookie de session partagé
   avec le site public, session dédiée, JWT, magic link, OAuth ?
3. **Quel périmètre fonctionnel pour la Phase 8C1 ?** Faut-il embarquer
   dès la première itération la gestion éditoriale, le reset de mot de
   passe HTTP, la MFA ?

## Options étudiées

### Rendu de l'administration

1. **Symfony + Twig SSR sous `/admin/**`, même origine que le site
   public** (retenu). L'admin est un ensemble de pages HTML rendues par
   Symfony ; Caddy route `/admin/*` sans strip vers `api:8000`, et le
   firewall Symfony `admin` s'accroche sur `^/admin`. Aucune API JSON
   dédiée à l'admin n'est exposée. La surface JS de l'admin est
   nulle : `script-src 'none'` dans la CSP.
2. Nuxt admin + API JSON Symfony dédiée (`/api/admin/*`). Rejeté :
   double frontière de confiance (auth côté Nuxt + auth côté Symfony),
   contrat JSON à versionner, exposition d'une surface API sensible
   uniquement pour un besoin d'IHM interne, complexification du CSRF
   (double token stateless côté API vs form token côté Twig).
3. Sous-domaine dédié `admin.devzair.fr` avec cookie propre. Rejeté :
   ajoute une gestion DNS et TLS, ne réduit pas la surface d'attaque
   (le cookie public reste absent : le site public est déjà stateless),
   complique le cycle dev (certificat local, hosts). Le préfixe
   `/admin/**` sur le même domaine suffit — le cookie `DZ_ADMIN_SESSID`
   est déjà cloisonné par nom, chemin `/`, `SameSite=Lax`.

### Preuve d'identité côté serveur

1. **Cookie de session Symfony dédié, form login + CSRF** (retenu).
   Symfony gère le cycle standard : formulaire POST, validation
   `UserProvider` (normalise l'email), `UserChecker` (refuse
   `is_active = false`), password hasher Argon2id, session régénérée
   à la connexion (`session_fixation_strategy: migrate`). Le cookie
   `DZ_ADMIN_SESSID` porte un nom dédié, `HttpOnly`, `SameSite=Lax`,
   `Secure` en prod (`cookie_secure: auto`).
2. JWT stateless (Authorization Bearer). Rejeté : impose une gestion
   client JS pour transporter le token, pas d'invalidation immédiate
   côté serveur en cas de compromission, surface API supplémentaire
   inutile pour du SSR.
3. Magic link email. Rejeté : dépendance forte au canal mail (déjà
   fragile en 8A2), UX qui pousse à copier-coller un lien sensible,
   fenêtre d'attaque replay large. Deviendra pertinent si un jour on
   ouvre l'admin à des rôles éditoriaux non-techniques ; hors périmètre
   Phase 8C1.
4. OAuth / OIDC via IdP externe. Rejeté : nécessite un IdP à
   provisionner, coût récurrent, complexité disproportionnée pour une
   agence à taille humaine avec un ou deux comptes admin.

### Rotation de la session

1. **`session_fixation_strategy: migrate` (défaut Symfony, déclaré
   explicitement)** (retenu). L'identifiant de session change à la
   connexion : tout cookie qu'un attaquant aurait injecté avant login
   (fixation) devient invalide immédiatement après authentification.
2. Aucune rotation. Rejeté : ouvre la porte à la fixation.

### Protection anti-brute-force

1. **`login_throttling` natif Symfony, 5 tentatives par 15 minutes**
   (retenu). Symfony pose deux fenêtres, `_login_local_admin` (par
   couple email+IP) et `_login_global_admin` (par IP), toutes deux
   backed par `cache.rate_limiter`. Les valeurs sont figées dans
   `security.yaml` — les factories du security bundle sont résolues
   à la compilation du container et n'acceptent pas `%env(...)%`.
2. Rate limiter custom en amont (Symfony `RateLimiter`). Rejeté :
   duplication de responsabilité, mauvaise intégration avec le firewall
   (les erreurs ne remontent pas comme un login échoué).
3. Aucune limite. Rejeté (brute force offline sur un hash Argon2id
   ralentit un attaquant, mais rien n'empêche un attaquant réseau de
   tester des millions de mots de passe côté serveur).

### Protection CSRF

1. **Deux token IDs distincts : `authenticate` (login POST),
   `logout` (POST /admin/logout)** (retenu). Chaque form Twig émet
   son propre token via `csrf_token()`. Le firewall Symfony vérifie
   automatiquement grâce à `enable_csrf: true` + `csrf_parameter:
   _csrf_token` sur les deux blocs `form_login` et `logout`.
2. Token unique partagé. Rejeté : le principe de moindre privilège
   veut qu'un token de login volé ne serve pas au logout et vice versa,
   même si l'exploitation reste théorique côté form SSR.

### Rôles et modèle utilisateur

1. **Rôle unique implicite `ROLE_ADMIN`, pas de colonne `roles` en
   base** (retenu). L'entité `AdminUser` déclare `getRoles():
   ['ROLE_ADMIN']` en dur ; le firmware `access_control` applique
   `ROLE_ADMIN` à toute route `^/admin` hors `/admin/login`. Un jour où
   plusieurs rôles seront nécessaires (éditeur, publicateur), une
   migration ajoutera la colonne — le contrat public de Twig et du
   firewall restera stable.
2. Colonne `roles` JSON dès le départ. Rejeté : YAGNI, ajoute une
   surface de bug (validation, migration, tests) sans besoin réel.

### Fournisseur d'utilisateur

1. **`AdminUserProvider` custom, indexe par
   `AdminEmail::normalized`** (retenu). Le VO `AdminEmail` normalise
   (`mb_strtolower(trim($email))`) avant lookup Doctrine ; l'index
   unique en base porte sur la colonne `normalized_email`. Un login
   avec `Admin@Devzair.Local` retrouve le compte enregistré sous
   `admin@devzair.local` sans doublon.
2. `EntityUserProvider` standard sur la colonne `email`. Rejeté :
   sensible à la casse et aux espaces bords, pouvant créer des
   comptes fantômes lors des créations CLI.

### Vérification pré-authentification

1. **`AdminUserChecker` custom, refuse `is_active = false` via
   `DisabledException`** (retenu). La commande CLI
   `app:admin:disable` bascule un compte compromis ou obsolète sans
   suppression physique (préserve l'historique des logs).
2. Suppression physique du compte. Rejeté : perte d'audit, casse
   toute relation future (auteur d'un article, par exemple).

### Cookie de session

1. **Nom dédié `DZ_ADMIN_SESSID`, `HttpOnly`, `SameSite=Lax`,
   `Secure=auto`, `lifetime` + `gc_maxlifetime` alignés sur
   `ADMIN_SESSION_LIFETIME`** (retenu). Le nom est distinct pour éviter
   toute confusion avec un futur cookie public émis par Nuxt (le
   domaine public reste stateless en Phase 8). `SameSite=Lax`
   autorise le retour depuis un lien externe légitime sans effet de
   bord : les actions d'écriture Phase 8C1 se limitent à
   `POST /admin/login` et `POST /admin/logout`, tous deux protégés
   CSRF.
2. `SameSite=Strict`. Rejeté : casse le retour depuis un signet
   externe (le navigateur n'envoie pas le cookie sur la première
   requête cross-site), rétrograde l'UX sans gain sécurité mesurable.
3. Cookie public partagé. Sans objet — le site public n'a pas de
   session.

### Stockage de session

1. **Filesystem local (`handler_id: null`)** (retenu Phase 8C1). Un
   seul conteneur `api`, pas de scaling horizontal en Phase 8, pas
   de Redis dans la stack.
2. Redis / Memcached. Rejeté prématurément : introduit une dépendance
   d'infrastructure sans besoin. À réévaluer le jour où plusieurs
   instances `api` tournent en parallèle.

### Logout

1. **POST uniquement, CSRF logout token, session invalidée**
   (retenu). `POST /admin/logout` porte un `_csrf_token` (id
   `logout`) ; le firewall `invalidate_session: true` purge la
   session serveur et efface le cookie.
2. GET logout. Rejeté : violation RFC 7231 (les GET doivent être
   idempotents), permet le CSRF trivial via `<img src>` ou
   pré-fetching de navigateur.

### CSP admin

1. **`default-src 'none'; script-src 'none'; style-src 'self';
   img-src 'self' data:; font-src 'self'; form-action 'self';
   base-uri 'none'; frame-ancestors 'none'`, styles externalisés
   dans `apps/api/public/admin/assets/admin.css`** (retenu).
   `script-src 'none'` élimine toute exécution JS possible. Les
   styles Twig sont sortis du `<style>` inline vers un fichier
   statique servi par Symfony (php -S built-in router), rendant
   `'unsafe-inline'` inutile.
2. Conserver `'unsafe-inline'` sur `style-src`. Rejeté : n'apporte
   aucune contrainte technique réelle (aucun style dynamique en
   Phase 8C1) et affaiblit la CSP sans raison. Un attaquant qui
   trouverait un point d'injection HTML sans JS pourrait toujours
   modifier la présentation (redirect visuel via `position: fixed`,
   phishing overlay) — la CSP stricte le bloque.
3. Nonces CSP. Rejeté : nonce complique la génération HTML pour du
   CSS statique. On les réintroduira le jour où l'admin embarquera
   du JS ou des styles inline dynamiques.

### Headers défensifs additionnels

1. **`X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`,
   `Referrer-Policy: no-referrer`, `Permissions-Policy: camera=(),
   microphone=(), geolocation=(), interest-cohort=()`,
   `Cross-Origin-Opener-Policy: same-origin`,
   `Cross-Origin-Resource-Policy: same-origin`, `X-Robots-Tag:
   noindex, nofollow`** (retenu). Un `AdminSecurityHeadersSubscriber`
   (priorité `-10` sur `kernel.response`) applique ces headers sur
   toute réponse dont le chemin commence par `/admin`. Les autres
   domaines HTTP (contact, éditorial, health) ne sont pas touchés.
2. Poser ces headers globalement. Rejeté : `X-Frame-Options: DENY`
   sur une page publique bloque toute intégration légitime (widget,
   preview). Le scoping `/admin` est intentionnel.

### Indexation par les moteurs

1. **Header `X-Robots-Tag: noindex, nofollow` sur `/admin/*` +
   directive `Disallow: /admin` (et `/admin/`) dans le robots.txt
   Nuxt, y compris pour les groupes IA (`GPTBot`, `Google-Extended`,
   `ClaudeBot`, etc.)** (retenu). Défense en profondeur : le robots
   TXT protège du crawl, le header protège de l'indexation même si un
   lien externe est publié.
2. Robots.txt seul. Rejeté : `robots.txt` est purement déclaratif ; un
   crawler qui l'ignore pourrait indexer le formulaire de login.

### Journalisation

1. **Canal Monolog dédié `admin`, événements `admin.login_failure`
   (short class name de l'exception uniquement) et `admin.logout`
   (UUID admin uniquement)** (retenu). Aucune PII n'est journalisée
   (pas d'email, pas d'IP dans le message applicatif — l'IP reste
   dans le log Caddy en amont). Un `AdminAuditListener` écoute
   `LoginFailureEvent` et `LogoutEvent`. Un
   `RecordSuccessfulLoginListener` met à jour `last_login_at` sur
   l'entité `AdminUser` (persistance Doctrine).
2. Log applicatif riche (email tapé, IP, user-agent). Rejeté :
   duplique le log Caddy pour l'IP et introduit une base de PII
   supplémentaire à protéger.
3. Table d'audit persistante en base. Rejeté prématurément pour
   Phase 8C1 (pas de besoin conformité formalisé, pas d'UI d'audit
   côté admin). À reconsidérer si un jour l'admin devient
   multi-utilisateur ou soumis à un référentiel type ISO 27001.

### Gestion CLI des comptes

1. **Trois commandes Symfony sous le namespace `app:admin:*` :
   `create-user`, `reset-password`, `disable`, scopées `#[When(env:
   'dev|test|prod')]` sans exception** (retenu). Les commandes
   acceptent le mot de passe uniquement via `--password-stdin` ou
   `askHidden()` interactif — jamais via `--password=xxx` (fuite
   assurée dans `ps`, l'historique shell, les logs de wrapper). Les
   trois opérations sont idempotentes : `disable` sur un compte déjà
   désactivé ne fait rien.
2. UI HTTP de gestion des admins (création, reset). Rejeté pour
   Phase 8C1 : ouvre une surface CSRF et gestion d'erreurs
   supplémentaire pour une opération rare, exécutée par un opérateur
   qui a déjà accès au conteneur.
3. Seeder au démarrage. Rejeté : reproduit un pattern anti-pattern
   (mot de passe par défaut publié).

### Reset de mot de passe côté HTTP

1. **Aucun reset HTTP en Phase 8C1** (retenu). L'opérateur exécute
   `docker compose exec api php bin/console app:admin:reset-password
   --email=admin@... --password-stdin`. Zéro dépendance mail, zéro
   token à faire vivre.
2. Reset par email avec token à durée limitée. Rejeté pour 8C1 :
   dépend d'un canal mail fiable, ajoute une surface (endpoint
   demande, endpoint validation, stockage token). À reconsidérer en
   Phase 8C4 si l'admin passe à plus d'un opérateur.

### MFA

1. **Pas de MFA en Phase 8C1** (retenu). Le compte est unique, protégé
   par un mot de passe long (Argon2id), le throttling limite le
   brute-force en ligne, la surface d'attaque est réduite (pas de JS,
   pas d'API JSON).
2. TOTP obligatoire. Rejeté pour 8C1 (une seule chose à la fois),
   à réévaluer avant ouverture prod si l'exposition change.

### Périmètre fonctionnel Phase 8C1

1. **Deux vues seulement : `/admin/login` (formulaire) et `/admin`
   (dashboard = profil + logout)** (retenu). Aucun CRUD, aucune
   création éditoriale, aucune gestion utilisateurs via HTTP.
2. Embarquer directement les vues éditoriales. Rejeté : trop de
   surface pour une itération authentification, empêche de
   stabiliser le socle avant d'y greffer les cas d'usage.

## Décision

### Topologie HTTP

- Caddy route `/admin` et `/admin/*` vers `api:8000` sans
  `strip_prefix` : le firewall Symfony doit voir le préfixe pour
  matcher `^/admin`. Les headers `X-Forwarded-Proto`,
  `X-Forwarded-For`, `X-Forwarded-Host`, `X-Forwarded-Port` sont
  transmis (Symfony reconstruit correctement les URLs absolues type
  `Location: /admin` derrière le port frontal `:3001` en dev,
  `:443` en prod).
- Les routes publiques (`/api/*`, tout le reste vers Nuxt) restent
  inchangées.

### Firewall Symfony

- `security.firewalls.admin` :
  - `pattern: ^/admin`
  - `provider: admin_users` (id `AdminUserProvider`)
  - `user_checker: AdminUserChecker`
  - `lazy: true`
  - `form_login` sur `admin_login`, `check_path: admin_login`,
    `username_parameter: email`, `password_parameter: password`,
    `default_target_path: admin_dashboard`, `post_only: true`,
    `enable_csrf: true`, `csrf_token_id: authenticate`.
  - `login_throttling`, `max_attempts: 5`, `interval: '15 minutes'`.
  - `logout` sur `admin_logout`, `target: admin_login`,
    `invalidate_session: true`, `enable_csrf: true`,
    `csrf_token_id: logout`.
- `access_control` : `^/admin/login$` → `PUBLIC_ACCESS`,
  `^/admin/assets/` → `PUBLIC_ACCESS`, `^/admin` → `ROLE_ADMIN`.
- `session_fixation_strategy: migrate` explicite.
- `password_hashers[App\Admin\Domain\AdminUser]: 'auto'` (Argon2id
  en prod) ; override `bcrypt cost 4` en test uniquement.

### Modèle domaine

- Table `admin_user` : colonnes `id` (UUID v7),
  `email` (VARCHAR 180 unique), `normalized_email` (VARCHAR 180
  unique, indexe le lookup), `display_name` (VARCHAR 120),
  `password_hash` (VARCHAR 255), `is_active` (BOOLEAN default true),
  `last_login_at` (TIMESTAMPTZ null), `created_at` (TIMESTAMPTZ),
  `updated_at` (TIMESTAMPTZ).
- Pas de colonne `roles` : `getRoles()` retourne `['ROLE_ADMIN']`
  en dur.
- VO `AdminEmail` (validation regex `[^\s@]+@[^\s@]+\.[^\s@]+`,
  longueur ≤ 180, normalisation `mb_strtolower(trim())`).
- Invariants (`AdminUserInvariantViolation`) : email valide,
  `displayName` non vide ≤ 120 caractères, `passwordHash` non vide.

### Session et cookie

- Nom de cookie : `DZ_ADMIN_SESSID`.
- `HttpOnly: true`, `SameSite: Lax`, `Secure: auto` (HTTPS en prod
  strictement).
- `cookie_lifetime` = `gc_maxlifetime` =
  `%env(int:ADMIN_SESSION_LIFETIME)%` (par défaut 28 800 s = 8 h).
- Stockage : filesystem local (`handler_id: null`).

### Sécurité applicative

- CSRF `authenticate` sur `POST /admin/login`, `logout` sur
  `POST /admin/logout`.
- `session_fixation_strategy: migrate` rejette toute session
  pré-authentification.
- `login_throttling` 5/15 min bloque les 6e à Nème tentatives par
  couple email+IP et par IP.
- `AdminUserChecker::checkPreAuth` lève `DisabledException` si
  `is_active = false`.
- Message d'erreur affiché systématiquement générique
  (« Identifiants invalides »), aucune divulgation de l'existence
  d'un compte.

### Headers HTTP admin (subscriber `AdminSecurityHeadersSubscriber`)

Appliqués sur toute réponse dont le path commence par `/admin` :

- `Content-Security-Policy: default-src 'none'; script-src 'none';
  style-src 'self'; img-src 'self' data:; font-src 'self';
  form-action 'self'; base-uri 'none'; frame-ancestors 'none'`.
- `X-Frame-Options: DENY`.
- `X-Content-Type-Options: nosniff`.
- `Referrer-Policy: no-referrer`.
- `Permissions-Policy: camera=(), microphone=(), geolocation=(),
  interest-cohort=()`.
- `Cross-Origin-Opener-Policy: same-origin`.
- `Cross-Origin-Resource-Policy: same-origin`.
- `X-Robots-Tag: noindex, nofollow`.

Le CSS admin est externalisé dans
`apps/api/public/admin/assets/admin.css` (servi statiquement par
Symfony `php -S`), aucun `<style>` inline dans les templates.
`unsafe-inline` supprimé de `style-src`.

### Non-indexation

- `X-Robots-Tag` sur toutes les réponses `/admin/*` (défense en
  profondeur côté serveur).
- `<meta name="robots" content="noindex, nofollow">` dans le
  layout Twig.
- `robots.txt` Nuxt refuse `/admin` et `/admin/` pour tous les
  bots, y compris `GPTBot`, `Google-Extended`, `ClaudeBot`,
  `PerplexityBot`, `CCBot` et autres crawlers IA (voir
  `nuxt.config.ts`).

### Journalisation (canal Monolog `admin`)

- `AdminAuditListener` (via `AsEventListener`) écoute
  `LoginFailureEvent` et `LogoutEvent` :
  - `admin.login_failure` : contexte réduit au short class name de
    l'exception (`BadCredentialsException`, `DisabledException`,
    `TooManyLoginAttemptsAuthenticationException`, ...). Aucun email,
    aucune IP dans le message applicatif — l'IP reste dans le log
    Caddy en amont.
  - `admin.logout` : uniquement l'UUID `admin_id`.
- `RecordSuccessfulLoginListener` met à jour `last_login_at` sur
  l'entité `AdminUser` (persistance Doctrine, à ne pas confondre
  avec un audit trail).
- Pas de table d'audit persistante (voir option 3 rejetée).

### Interfaces d'administration CLI

Trois commandes Symfony, toutes idempotentes, exposées uniquement en
`dev|test|prod` :

- `app:admin:create-user --email=... --display-name=...
  --password-stdin` (ou askHidden interactif).
- `app:admin:reset-password --email=... --password-stdin` (ou
  askHidden).
- `app:admin:disable --email=...` (bascule `is_active = false`).

Aucun mot de passe n'est jamais transmis via un argument nommé
`--password=xxx` (fuite dans `ps`/historique/logs).

### Ce que la Phase 8C1 ne fait PAS

- Aucune capacité éditoriale (création, édition, publication
  d'article). L'agrégat `Article` n'est pas modifié.
- Aucune API JSON dédiée à l'admin.
- Aucun reset de mot de passe par email.
- Aucune MFA.
- Aucun rôle autre que `ROLE_ADMIN`.
- Aucun IdP externe (OAuth/OIDC/SAML).
- Aucun sous-domaine `admin.*`.
- Aucune table d'audit persistante (log Monolog uniquement).
- Aucun scaling horizontal du stockage de session (filesystem
  local suffit tant qu'il n'y a qu'un conteneur `api`).

### Runtime config

- `ADMIN_SESSION_LIFETIME` (secondes, défaut 28 800 = 8 h).
- `ADMIN_LOGIN_MAX_ATTEMPTS` : documenté dans `.env.example` mais
  non consommé par `security.yaml` (les factories du security
  bundle sont résolues à la compilation) — sert de contrat
  documentaire pour un futur ajustement (nécessitera édition du
  YAML).
- `ADMIN_LOGIN_INTERVAL` (ex. `"15 minutes"`) : idem.

## Raisons

- **Réduire la surface d'attaque au minimum viable.** Pas de JS
  admin, pas d'API JSON admin, pas de dépendance externe (IdP,
  mail, Redis). Chaque composant supplémentaire est un vecteur
  d'attaque et une dette de maintenance.
- **Cloisonner strictement la surface authentifiée.** Cookie dédié
  (`DZ_ADMIN_SESSID`) sur le même origin, headers défensifs scopés
  `/admin`, refus d'indexation, ROLE_ADMIN obligatoire au-delà de
  `/admin/login`.
- **Respecter les défauts éprouvés de Symfony.** `form_login`,
  `login_throttling`, `session_fixation_strategy: migrate`,
  Argon2id auto — le bundle Security fournit un socle audité et
  maintenu. Nos custom se limitent à `UserProvider` (normalisation
  email) et `UserChecker` (compte désactivé).
- **Ne rien divulguer sur l'existence d'un compte.** Message
  générique unique (« Identifiants invalides »), logs sans PII.
- **Séparer administration technique et administration
  fonctionnelle.** La gestion des comptes admin est un acte
  d'exploitation (CLI depuis le conteneur `api`), pas un cas d'usage
  produit. L'ouvrir en HTTP nécessiterait audit, MFA, et un
  processus d'invitation — hors Phase 8C1.
- **Pouvoir grossir sans réécriture.** Le passage à Redis pour la
  session, à un audit trail persistant, à la MFA, ou à des rôles
  multiples se fera par ajout — pas par retrait — grâce aux
  interfaces `AdminUserRepositoryInterface`, `AdminAccountService`,
  et au canal Monolog dédié.

## Conséquences

**Positives**

- Une frontière d'authentification unique et lisible : firewall
  Symfony sur `^/admin`, session cookie dédié, headers défensifs
  scopés, robots.txt cohérent.
- Contrat d'IHM éditoriale (Phase 8C2+) déjà encadré : il suffira
  d'ajouter des routes et templates sous `/admin/*` pour bénéficier
  automatiquement du firewall, de la CSP, du logging et des
  restrictions d'indexation.
- Tests intégration Symfony (PHPUnit `WebTestCase`) et E2E
  navigateur (Playwright via Caddy) déterministes grâce aux
  overrides `services_test.yaml` : session `mock_file`,
  `InMemoryStorage` pour les deux fenêtres de throttling
  (`_login_local_admin` / `_login_global_admin`), bcrypt cost 4
  pour compresser le temps de hash.
- Aucune dépendance runtime ajoutée (Redis, IdP, mail) : l'admin
  fonctionne dans la même stack Docker que le reste.

**Limites / risques**

- Stockage de session filesystem local : ne survit pas au
  redémarrage du conteneur, ne se partage pas entre plusieurs
  instances `api`. Acceptable en Phase 8 (single instance) ; à
  migrer vers Redis avant tout scaling horizontal.
- Pas de MFA : un mot de passe compromis ouvre l'admin. Le
  throttling limite le brute-force en ligne mais pas le vol de
  mot de passe. À réévaluer avant que l'admin ne devienne
  multi-utilisateur ou n'expose des données sensibles en écriture.
- Pas de reset HTTP : perdre le mot de passe = exécuter
  `app:admin:reset-password` depuis le conteneur. Acceptable pour
  un compte opérateur unique.
- Pas de table d'audit persistante : la journalisation Monolog
  suffit pour les besoins de troubleshooting mais pas pour un
  référentiel conformité formel.
- Login throttling non paramétrable par env : ajuster
  `max_attempts` ou `interval` nécessite édition de
  `security.yaml` + redéploiement.
- La CSP durcie (`style-src 'self'` sans `unsafe-inline`) impose
  que tout futur style admin passe par un fichier CSS statique
  ou par un mécanisme de nonce (à introduire par nouvelle ADR).

**Travaux associés (Phase 8C1, hors périmètre ADR)**

- Externalisation du CSS admin dans
  `apps/api/public/admin/assets/admin.css` (fichier statique servi
  par le routeur php -S de Symfony) et mise à jour de la CSP.
- Access control `^/admin/assets/` → `PUBLIC_ACCESS` pour que le
  firewall n'exige pas d'authentification sur le CSS statique.
- Robots.txt Nuxt : `Disallow: /admin` (et `/admin/`) pour tous
  les groupes de bots incluant IA.

**Phases suivantes séparées de la 8C1**

- **8C2** — mise à disposition d'une IHM éditoriale (liste des
  articles, création, édition brouillon).
- **8C3** — publication depuis l'IHM (transition brouillon →
  publié, invalidations de cache).
- **8C4** — durcissements complémentaires (MFA, table d'audit
  persistante, reset HTTP si besoin, éventuel Redis pour la
  session).

Chaque phase suivante fera l'objet d'une décision ou d'un ADR
propre si elle change une frontière posée ici.

## Références

- `apps/api/config/packages/security.yaml` — firewall `admin`,
  form_login, login_throttling, logout CSRF.
- `apps/api/config/packages/framework.yaml` — session
  `DZ_ADMIN_SESSID`, cookie flags, lifetime piloté par env.
- `apps/api/config/routes/admin.yaml` — routes `admin_login`,
  `admin_dashboard`.
- `apps/api/config/routes/security.yaml` — logout route générée
  par le security bundle.
- `apps/api/config/services.yaml` — canal Monolog `admin`, alias
  `AdminUserRepositoryInterface`, clock injectable.
- `apps/api/config/services_test.yaml` — overrides InMemoryStorage
  pour `_login_local_admin` et `_login_global_admin`, `NativeClock`
  public.
- `apps/api/src/Admin/Domain/AdminUser.php` — entité,
  `getRoles(): ['ROLE_ADMIN']`, `getUserIdentifier()` =
  `normalized_email`.
- `apps/api/src/Admin/Domain/AdminEmail.php` — VO, normalisation
  `mb_strtolower(trim())`.
- `apps/api/src/Admin/Infrastructure/Security/AdminUserProvider.php`
  — lookup par email normalisé, `UserNotFoundException` générique.
- `apps/api/src/Admin/Infrastructure/Security/AdminUserChecker.php`
  — `DisabledException` sur `is_active = false`.
- `apps/api/src/Admin/Infrastructure/Security/AdminAuditListener.php`
  — logs `admin.login_failure` (class name), `admin.logout`
  (admin_id).
- `apps/api/src/Admin/Infrastructure/Security/RecordSuccessfulLoginListener.php`
  — mise à jour `last_login_at`.
- `apps/api/src/Admin/Application/AdminAccountService.php` —
  createAdmin, resetPassword, disableAdmin.
- `apps/api/src/Admin/Presentation/Console/CreateAdminUserCommand.php`
  — `app:admin:create-user`, `--password-stdin` uniquement.
- `apps/api/src/Admin/Presentation/Console/ResetAdminPasswordCommand.php`
  — `app:admin:reset-password`, `--password-stdin` uniquement.
- `apps/api/src/Admin/Presentation/Console/DisableAdminUserCommand.php`
  — `app:admin:disable`, idempotent.
- `apps/api/src/Admin/Presentation/EventSubscriber/AdminSecurityHeadersSubscriber.php`
  — CSP durcie, headers défensifs scopés `/admin/*`.
- `apps/api/src/Admin/Presentation/Http/AdminLoginController.php`
  — rend le formulaire, redirige les authentifiés vers
  `admin_dashboard`.
- `apps/api/src/Admin/Presentation/Http/AdminDashboardController.php`
  — `#[IsGranted('ROLE_ADMIN')]`.
- `apps/api/templates/admin/*.twig` — layout, login, dashboard, CSS
  externalisé dans `public/admin/assets/admin.css`.
- `apps/api/migrations/Version20260805221410.php` — table
  `admin_user`.
- `apps/web/nuxt.config.ts` — robots.txt refusant `/admin` pour
  tous les bots.
- `apps/web/test/e2e/admin.spec.ts` — E2E Axe + login/logout via
  Caddy sur `:3001`.
- `infra/caddy/Caddyfile` — routing `/admin` sans strip_prefix,
  forwarding des `X-Forwarded-*`.
- ADR-006 — runtime Symfony 7.4 LTS + Caddy.
- ADR-009 — persistance PostgreSQL Doctrine (UUID v7).
