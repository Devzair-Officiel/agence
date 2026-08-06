# apps/api — Backend Symfony 7.4 LTS (Phases 6A / 6C / 8A / 8B1 / 8B2 / 8C1 / 8C2 / 8C3)

API HTTP + IHM admin de l'agence Devzair.

- Domaine `Contact` (Phases 6A + 6C) : `POST /api/contact` (soumission du
  formulaire) + `GET /api/health` (santé) + commande CLI de diagnostic
  `bin/console app:contact:check`. Aucun stockage, envoi email
  synchrone.
- Domaine `Editorial` (Phases 8A + 8B1 + 8C2 + 8C3) : `GET /api/resources`
  (liste paginée des articles publiés) + `GET /api/resources/{slug}`
  (article publié par slug), avec cache HTTP conditionnel (ETag faible
  + `Last-Modified` sur le détail + 304 Not Modified). Persistance
  PostgreSQL 17-alpine via Doctrine ORM 3. La lecture publique n'expose
  que les articles `Published` avec `publishedAt <= now`. L'alimentation
  se fait soit par les commandes CLI
  `bin/console app:editorial:import <path> [--dry-run]` et
  `bin/console app:editorial:publish <slug> [--published-at=<ISO8601>]`
  (Phase 8B1 — aucun endpoint HTTP JSON d'écriture, cf. ADR-010), soit
  par l'IHM éditoriale admin sous `/admin/articles/**` (Phase 8C3 —
  Twig SSR, POST + CSRF, aucun endpoint JSON admin).
- Domaine `Admin` (Phases 8C1 + 8C3) : firewall Symfony `admin` sur
  `^/admin` (cookie session dédié `DZ_ADMIN_SESSID`, CSP `default-src
  'none'; style-src 'self'`, throttling 5/15min, CSRF sur toutes les
  mutations). Phase 8C1 = socle auth + CLI `app:admin:*`. Phase 8C3 =
  IHM éditoriale `/admin/articles/**` (liste paginée + filtre statut,
  création brouillon, édition draft-only, publication depuis IHM,
  archivage, restauration), rate-limiting par UUID admin
  (`AdminActionRateLimiter` : `admin_write` 30/min, `admin_publish`
  10/min), audit sans PII (canal Monolog `admin`, UUID admin
  uniquement).

Voir les décisions consignées :

- `docs/adr/ADR-006-runtime-symfony-caddy.md` — runtime PHP + reverse proxy.
- `docs/adr/ADR-007-endpoint-contact-securite.md` — sécurité endpoint.
- `docs/adr/ADR-008-mailer-ovhcloud-turnstile-optionnel.md` — transport
  SMTP OVHcloud, Turnstile facultatif (deux flags alignés) et
  réponse HTTP 503 `temporary_error` sur échec Mailer.
- `docs/adr/ADR-009-persistance-postgresql-editorial.md` — PostgreSQL 17-alpine
  verrouillée, Doctrine ORM 3 par attributs sur l'entité `Article`,
  UUID v7 direct, `expertise_ids` en `jsonb`, markdown stocké brut.
- `docs/adr/ADR-010-pipeline-markdown-editorial-cache-http.md` —
  pipeline CLI d'import/publication Markdown (`app:editorial:import`
  create-only + `app:editorial:publish` idempotent), refus des dates
  futures (double garde-fou agrégat + lecture), cache HTTP conditionnel
  faible (`ETag` sur les deux endpoints, `Last-Modified` uniquement
  sur le détail, `X-Request-Id` préservé sur 304).
- `docs/adr/ADR-011-ssr-nuxt-editorial-cache-nitro.md` — SSR Nuxt des
  pages `/ressources/**` et frontière du cache Nitro
  (`useStorage("editorial")`).
- `docs/adr/ADR-012-administration-symfony-ssr-authentifiee.md` —
  administration Symfony/Twig SSR sous `/admin/**` (Phase 8C1 +
  extensions 8C3 documentées via DEC-087..091 dans
  `docs/10-TRACKING.md`).
- `docs/checklists/PRODUCTION-CONTACT.md` — séquence opérationnelle de
  mise en prod du formulaire (SPF/DKIM/DMARC, pré-déploiement,
  test réel maîtrisé, dégradation contrôlée, rollback).

## Stack

- PHP 8.4 CLI (serveur intégré `php -S` en dev — voir ADR-006 pour la
  migration prévue vers FrankenPHP ou PHP-FPM en production). Extension
  `pdo_pgsql` requise (dev + CI + prod).
- Symfony 7.4 LTS avec les composants suivants uniquement :
  - `symfony/framework-bundle` — MicroKernel ;
  - `symfony/serializer` + `symfony/validator` — DTO avec contraintes ;
  - `symfony/http-client` — vérification Cloudflare Turnstile ;
  - `symfony/mailer` + `symfony/mime` — envoi email (défaut null://null) ;
  - `symfony/rate-limiter` — token bucket par IP ;
  - `symfony/monolog-bundle` — logs structurés canaux `contact` / `editorial` ;
  - `symfony/uid` — `Request-Id` UUID v7 + identifiant `Article`.
- Doctrine (Phase 8A) : `doctrine/orm ^3.3` + `doctrine/doctrine-bundle ^2.13`
  + `doctrine/doctrine-migrations-bundle ^3.4`. Mapping par attributs
  restreint à `App\Editorial\Domain` uniquement — le domaine `Contact`
  reste sans ORM.
- PostgreSQL 17-alpine (dev + CI + prod, version verrouillée par ADR-009).
- Twig (Phase 8C1) : rendu SSR de l'administration sous `/admin/**`
  (`symfony/twig-bundle`). L'admin est same-origin, sans surface JS
  (CSP `script-src 'none'`) — voir ADR-012.

## Architecture

Convention AGENTS.md §6 (contrôleur → service → interface remplaçable).
Le domaine `Editorial` (Phase 8A) suit une découpe en 4 couches
(Domain / Application / Infrastructure / Presentation) et est isolé du
domaine `Contact` (aucune dépendance croisée).

```
src/
  Contact/
    Controller/
      ContactSubmissionController.php    ← POST /api/contact
      HealthController.php               ← GET /api/health
    Dto/
      ContactRequest.php                 ← contraintes de validation
    Service/
      SubmitContactMessage.php           ← cas d'usage
      ContactMessageSenderInterface.php  ← contrat stable
      SymfonyContactMessageSender.php    ← implémentation Mailer, mappe
                                            TransportExceptionInterface →
                                            ContactTemporarilyUnavailableException
      InMemoryContactMessageSender.php   ← fake pour tests (failNextTemporarily)
      ContactSubmissionResult.php        ← + STATUS_TEMPORARY_ERROR (503)
    Security/
      OriginAllowlist.php                ← CSRF Option B (Origin allowlist)
      ContactRateLimiter.php             ← RateLimiter token bucket
      TurnstileVerifierInterface.php     ← contrat CAPTCHA
      TurnstileVerifierFactory.php       ← sélection selon env
      AlwaysAllowTurnstileVerifier.php   ← dev/test
      CloudflareTurnstileVerifier.php    ← prod (Siteverify)
      TurnstileVerdict.php
    Configuration/                       ← Phase 6C
      ContactConfigurationIssue.php      ← VO readonly (error / warning)
      ContactConfigurationReport.php     ← liste d'issues + isValid()
      ContactConfigurationValidator.php  ← service pur, aucune I/O
    Command/                             ← Phase 6C
      ContactCheckCommand.php            ← bin/console app:contact:check
    Exception/                           ← Phase 6C
      ContactTemporarilyUnavailableException.php  ← marker de domaine
  Editorial/                             ← Phase 8A + 8B1 + 8C2 + 8C3
    Domain/                              ← modèle métier, sans I/O
      Article.php                        ← agrégat + attributs Doctrine (Phase 8C2 : +mutations draft-only)
      ArticleRepositoryInterface.php     ← port (Phase 8B1 : +findBySlug + $now ; Phase 8C2 : +findById)
      ArticleSlug.php                    ← VO (regex, longueur)
      ArticleStatus.php                  ← enum Draft/Published/Archived
      Author.php                         ← VO (organization/person)
      AuthorType.php                     ← enum
      ExpertiseIdentifier.php            ← enum 5 piliers (miroir Nuxt)
      SeoMetadata.php                    ← VO (title/description longueurs)
      Clock/
        ClockInterface.php               ← port horloge
        SystemClock.php                  ← implémentation runtime
      Exception/
        ArticleInvariantViolation.php
        ArticleNotFoundException.php     ← +forId(string) en Phase 8C2
        ArticleNotEditableException.php  ← Phase 8C2
        InvalidArticleTransitionException.php  ← Phase 8C2 (+cannotPublishFrom en 8C3)
        ArticleSlugAlreadyExistsException.php  ← Phase 8C3 (race concurrente Doctrine → domaine)
    Application/                         ← cas d'usage
      Command/                           ← Phase 8B1 (CLI) + 8C2 (mutations) + 8C3 (admin)
        ImportArticleFromMarkdown.php + Handler + Result  ← Phase 8B1
        PublishArticleBySlug.php + Handler + Result       ← Phase 8B1 (chemin CLI)
        UpdateDraftArticle.php + Handler + Result         ← Phase 8C2 (patch atomique)
        ArchiveArticleAction.php + Handler + Result       ← Phase 8C2 (idempotent)
        RestoreArticleAction.php + Handler + Result       ← Phase 8C2 (Archived → Draft)
        CreateDraftArticle.php + Handler + Result         ← Phase 8C3 (atomique + interception slug conflict)
        PublishDraftArticle.php + Handler + Result        ← Phase 8C3 (distinct de PublishArticleBySlug, refus strict Published/Archived)
      Markdown/                          ← Phase 8B1 — VO front matter + exceptions
        ArticleFrontMatter.php           ← refuse publishedAt / clés inconnues
        MarkdownParseException.php
        MarkdownValidationException.php  ← agrège toutes les violations d'un fichier
      Query/
        ListPublishedArticles.php        ← Phase 8A — paramètres validés
        ListPublishedArticlesHandler.php ← invoke → {items, pagination}
        GetPublishedArticle.php
        GetPublishedArticleHandler.php
        AdminArticleReadRepositoryInterface.php  ← Phase 8C3 — port CQRS admin distinct (cf. DEC-088)
        ListAdminArticles.php + Handler + AdminArticleListPage + AdminArticleListItem  ← Phase 8C3
        GetAdminArticleForEdit.php + Handler + AdminArticleEditView                    ← Phase 8C3
      View/
        ArticleSummaryView.php           ← sans body_markdown / seo
        ArticleDetailView.php            ← payload complet
        PaginationView.php               ← fromCount(page, perPage, total)
    Infrastructure/
      Persistence/
        DoctrineArticleRepository.php    ← implémente le port, filtre Published
        DoctrineAdminArticleReadRepository.php  ← Phase 8C3 (tri stable updated_at DESC, id DESC)
      Markdown/                          ← Phase 8B1
        MarkdownArticleFileParser.php    ← ≤ 512 Kio, UTF-8 sans BOM, YAML délimité
        MarkdownContentValidator.php     ← rejet AST HtmlBlock/HtmlInline + schémas d'URL
        MarkdownSecurityPolicy.php       ← fige CommonMark (test dédié garde-fou)
        CommonMarkArticleRenderer.php    ← dry-run CLI + préparation Phase 8B2
    Presentation/
      Console/                           ← Phase 8B1 — CLI
        ImportArticleCommand.php         ← app:editorial:import <path> [--dry-run]
        PublishArticleCommand.php        ← app:editorial:publish <slug> [--published-at=…]
      Http/
        ListPublishedArticlesController.php  ← GET /resources (+ ETag/304 Phase 8B1)
        GetPublishedArticleController.php    ← GET /resources/{slug} (+ ETag/Last-Modified/304)
        ConditionalCache/                ← Phase 8B1
          ArticleETag.php                ← W/"sha256(id|updatedAt|v1)"
          ArticleListETag.php            ← W/"sha256(page|perPage|total|v1 + items)"
  Admin/                                 ← Phase 8C1 + 8C3
    Domain/                              ← Phase 8C1 — AdminUser, AdminEmail (VO normalisé), port, exceptions
    Application/                         ← Phase 8C1 — AdminAccountService
    Infrastructure/
      Persistence/                       ← Phase 8C1 — DoctrineAdminUserRepository
      Security/                          ← Phase 8C1 — AdminUserProvider, AdminUserChecker ; Phase 8C3 — AdminActionRateLimiter (par UUID admin)
      Logging/                           ← Phase 8C3 — EditorialAdminAuditLogger (canal `admin`, UUID uniquement)
    Presentation/
      Console/                           ← Phase 8C1 — app:admin:create-user / reset-password / disable
      EventSubscriber/                   ← Phase 8C1 — AdminSecurityHeadersSubscriber (CSP durcie + noindex ; 8C3 : +Cache-Control private no-store)
      Http/                              ← Phase 8C1 — Login/Dashboard ; Phase 8C3 — AdminArticle{List,Create,Edit,Publish,Archive,Restore}Controller + Form/{ArticleCreateData,ArticleEditData,FormErrorBag,ArticleFormPayload}
  EventListener/
    ApiExceptionListener.php             ← toute exception → JSON
```

Migrations Doctrine : `apps/api/migrations/` (racine `App\Migrations`).

## Pipeline `POST /api/contact`

1. Génère un `Request-Id` (UUID v7) porté par la réponse et tous les logs.
2. Rejette si `Origin` absent ou non listé → 403 `origin_not_allowed`.
3. Rejette si payload > 10 KB → 413 `payload_too_large`.
4. Consomme un jeton du rate limiter (par IP) → 429 + `Retry-After` sinon.
5. Deserialize JSON. Erreur → 400 `invalid_json`.
6. Honeypot : `website` non vide → 202 générique, **aucun email envoyé**.
7. Validation DTO. Erreur → 400 `validation_failed` + `errors`.
8. Vérifie Turnstile côté serveur. Refus → 403 `turnstile_rejected`.
9. Compose l'email (`From` app, `Reply-To` visiteur, texte brut) et envoie.
10. Log `contact.submitted` + `request_id` + `project_type` uniquement.
    **Aucun PII loggué** : ni message, ni email visiteur, ni téléphone,
    ni token, ni secret.

## Pipeline `GET /api/resources` et `GET /api/resources/{slug}` (Phase 8A)

`GET /api/resources?page=1&per_page=10` :

1. Génère un `Request-Id` (UUID v7) porté par la réponse (corps +
   `X-Request-Id`) et tous les logs (canal `editorial`).
2. Valide `page ≥ 1` et `per_page ∈ [1, 50]` (défaut 10). Invalide →
   400 `validation_error` + `Cache-Control: no-store`.
3. Lit **uniquement** les articles `status = Published` via
   `ArticleRepositoryInterface::listPublished(page, perPage)` +
   `countPublished()` — jamais de fuite `Draft`/`Archived`.
4. Ordre stable : `publishedAt DESC, id DESC` (UUID v7 monotone
   lexicographiquement — cf. DEC-069).
5. Réponse 200 avec `{items: ArticleSummaryView[], pagination:
   {page, per_page, total, total_pages}, request_id}` et
   `Cache-Control: public, max-age=60, s-maxage=300`. `items[]` **ne
   contient pas** `body_markdown` ni `seo` (vue résumé).

`GET /api/resources/{slug}` :

1. Génère un `Request-Id` UUID v7.
2. Instancie `ArticleSlug` (VO — regex `[a-z0-9]+(?:-[a-z0-9]+)*`,
   3-120 chars). Slug malformé → 400 `validation_error`.
3. Cherche via `getPublishedBySlug(slug)`. Slug inconnu / non publié
   → 404 `not_found` + `Cache-Control: no-store`.
4. Réponse 200 avec `ArticleDetailView` (payload complet dont
   `body_markdown` brut — le renderer HTML est reporté Phase 8B2) et
   `Cache-Control: public, max-age=60, s-maxage=300`.

### Cache HTTP conditionnel (Phase 8B1)

Les deux endpoints publient un `ETag` **faible** (`W/"…"`, cf. DEC-076) —
le corps JSON contient un `request_id` unique par requête, un ETag fort
mentirait. Le détail publie en plus `Last-Modified` (basé sur
`updatedAt`) ; la liste paginée n'en publie pas (cf. DEC-077, sémantique
HTTP réservée à une ressource unique). `Cache-Control: public, max-age=60,
s-maxage=300` sur 200, `no-store` sur 4xx.

Sur `If-None-Match` correspondant (ou `If-Modified-Since` équivalent
côté détail), la réponse est `304 Not Modified` avec `X-Request-Id`
**réécrit explicitement** après `isNotModified()` (Symfony purge la
plupart des en-têtes non liés à la validation sur 304 — cf. DEC-078).
Le préfixe de version `v1` dans le matériau du hash permet une
invalidation en masse en un déploiement si le contrat JSON évolue.

## Pipeline CLI d'import Markdown (Phase 8B1)

L'alimentation de la base éditoriale est **exclusivement** locale, via
deux commandes exécutées dans le conteneur `api` (aucun endpoint HTTP
d'écriture — cf. ADR-010 et DEC-074). Les articles vivent en Markdown
avec YAML front matter délimité par `---` :

```markdown
---
slug: mon-article
title: Mon article de démonstration
excerpt: Un extrait affiché en liste et dans le meta description.
seo:
  title: Un titre SEO (30-70 caractères)
  description: Une description SEO entre 70 et 160 caractères pour respecter les bonnes pratiques d'indexation.
author:
  type: organization
  name: Devzair
expertises:
  - concevoir
---

# Corps Markdown

Contenu **standard** avec liens `[texte](https://example.com)`,
images, listes, code — pas de HTML brut, seuls les schémas d'URL
`http`, `https`, `mailto` et `tel` (les URL relatives sont
autorisées) sont acceptés.
```

Le champ `publishedAt` / `published_at` est **explicitement interdit**
dans le front matter — la publication est une action séparée.

### Import (`app:editorial:import`)

```bash
# Valider un fichier sans persister :
docker compose exec api php bin/console app:editorial:import \
    /chemin/vers/mon-article.md --dry-run

# Importer réellement (article créé en Draft) :
docker compose exec api php bin/console app:editorial:import \
    /chemin/vers/mon-article.md
```

Refus détaillés (parseur / validateur) — le rédacteur voit toutes les
violations d'un même fichier d'un coup :

- fichier > 512 Kio, BOM UTF-8, YAML manquant ou invalide ;
- clé racine / SEO / auteur inconnue ;
- `publishedAt` / `published_at` présent dans le front matter ;
- HTML brut (`HtmlBlock` / `HtmlInline`) dans le corps ;
- schéma d'URL hors liste blanche (`javascript:`, `data:`, `vbscript:`,
  `file:`, `ftp:` sont refusés) ;
- slug déjà présent en base (peu importe le statut — mode
  « create only », cf. DEC-074).

### Publication (`app:editorial:publish`)

```bash
# Publier à l'instant présent (Clock::now) :
docker compose exec api php bin/console app:editorial:publish mon-article

# Publier avec une date passée explicite (ISO 8601 UTC) :
docker compose exec api php bin/console app:editorial:publish mon-article \
    --published-at=2026-08-01T09:00:00+00:00
```

- Idempotent : un article déjà publié → warning + exit 0.
- `--published-at` future refusée (double garde-fou agrégat + lecture
  — cf. DEC-075).
- Slug inconnu → exit 1.

## Développement local

```bash
# Depuis la racine du monorepo :
docker compose up -d --build       # démarre caddy + web + api + postgres
curl -s http://localhost:3001/api/health
# → {"status":"ok"}

# Appliquer les migrations Doctrine sur la base dev (première fois) :
docker compose exec api php bin/console doctrine:migrations:migrate --no-interaction

# Aucun contenu d'article n'est fourni : la base démarre vide.
# La lecture publique renvoie donc { "items": [], "pagination": { "total": 0, … } }.
curl -s "http://localhost:3001/api/resources?page=1&per_page=10"


# Test rapide de contact (Origin obligatoire) :
curl -sX POST http://localhost:3001/api/contact \
     -H 'Content-Type: application/json' \
     -H 'Origin: http://localhost:3001' \
     -d '{
       "name":"Alice",
       "email":"alice@example.com",
       "projectType":"refonte",
       "message":"Un message de test suffisamment long pour passer.",
       "consent":true,
       "website":"",
       "turnstileToken":"dev-noop"
     }'
# → {"status":"accepted","request_id":"..."}
```

En dev, `TURNSTILE_ENABLED=false` (implémentation `AlwaysAllow`) et
`MAILER_DSN=null://null` (aucun envoi effectif). Ces défauts sont surs
tant que le formulaire côté navigateur n'existe pas.

## Contrôles qualité locaux

Composer et PHPUnit sont préinstallés dans l'image `api`. Pour lancer :

```bash
docker compose exec api composer validate --strict
docker compose exec api php bin/console lint:yaml config --parse-tags
docker compose exec api php bin/console lint:container --env=test
docker compose exec api php bin/console doctrine:migrations:migrate --no-interaction --env=test
docker compose exec api php bin/console doctrine:schema:validate --env=test --skip-sync
docker compose exec api vendor/bin/phpunit
```

En CI, le workflow `.github/workflows/api-quality.yml` reproduit cette
séquence sur chaque push et pull request touchant `apps/api/`. Il boote
un service `postgres:17-alpine` (aligné avec `compose.yaml`), crée
`devzair_test`, applique les migrations puis valide le schéma avant de
lancer PHPUnit — aucune connexion SMTP réelle, aucune requête réseau
externe, aucun envoi email.

Suites PHPUnit :

- `tests/Contact/*` — Phases 6A + 6C (DTO, endpoint, mailer, security,
  configuration validator, CLI diagnostic, logging sans PII).
- `tests/Editorial/Domain/*` — invariants agrégat, VO, enums.
- `tests/Editorial/Application/*` — handlers pilotés par
  `InMemoryArticleRepository` (support), aucune I/O.
- `tests/Editorial/Infrastructure/*` — `DoctrineArticleRepositoryTest`
  (KernelTestCase + transaction rollback en setUp/tearDown sur
  `devzair_test`).
- `tests/Editorial/Presentation/*` — WebTestCase contrat 200 / 400 /
  404 + headers `Cache-Control` + payload JSON.
- `tests/Editorial/Application/Markdown/*` — Phase 8B1 : `ArticleFrontMatterTest`
  (refus `publishedAt`, clés inconnues).
- `tests/Editorial/Infrastructure/Markdown/*` — Phase 8B1 :
  `MarkdownSecurityPolicyTest` (garde-fou configuration CommonMark),
  `MarkdownContentValidatorTest` (rejet AST + schémas d'URL),
  `MarkdownArticleFileParserTest`, `CommonMarkArticleRendererTest`.
- `tests/Editorial/Application/Command/*` — Phase 8B1 :
  `ImportArticleFromMarkdownHandlerTest`, `PublishArticleBySlugHandlerTest`.
- `tests/Editorial/Presentation/Console/*` — Phase 8B1 :
  `ImportArticleCommandTest`, `PublishArticleCommandTest` (via `CommandTester`).
- `tests/Editorial/Presentation/ConditionalCache/*` — Phase 8B1 :
  `GetPublishedArticleConditionalCacheTest`, `ListPublishedArticlesConditionalCacheTest`
  (ETag faible, 304 sur `If-None-Match`/`If-Modified-Since`, `X-Request-Id`
  préservé sur 304).
- `tests/Editorial/Support/*` — `FixedClock`, `InMemoryArticleRepository`,
  `ArticleBuilder`, trait `EntityManagerStub` (Phase 8B1),
  `MarkdownFixture` (Phase 8B1) — patterns partagés, jamais rendus publics.

Suite `Editorial` complète : **117 tests / 259 assertions**.

## Diagnostic de configuration (Phase 6C)

Avant toute mise en production du formulaire, exécuter dans
l'environnement cible :

```bash
docker compose exec api php bin/console app:contact:check --env=prod
```

La commande invoque le service pur `ContactConfigurationValidator`
(aucune I/O, aucun envoi email, aucune requête réseau) et retourne :

- `0` — configuration valide (peut afficher des avertissements assumés) ;
- `1` — erreurs bloquantes listées (`mailer_dsn_null_in_prod`,
  `mailer_dsn_plaintext_in_prod`, `recipient_missing`,
  `recipient_invalid`, `from_email_*`, `turnstile_secret_missing`,
  `origin_allowlist_empty`…).

La sortie **ne divulgue jamais** le DSN complet, le secret Turnstile ni
les emails en clair — elle peut être collée dans un ticket d'ops.
Séquence complète de mise en production dans
`docs/checklists/PRODUCTION-CONTACT.md`.

## Environnement

Toutes les variables sont documentées dans `.env.example` à la racine du
monorepo. Défauts sûrs :

| Variable                    | Défaut               | Utilité                                    |
|-----------------------------|----------------------|--------------------------------------------|
| `APP_ENV`                   | `dev`                | Environnement Symfony                      |
| `APP_SECRET`                | valeur factice       | Signature cookies internes (non utilisé)   |
| `MAILER_DSN`                | `null://null`        | Aucun envoi email par défaut               |
| `CONTACT_RECIPIENT`         | *vide*               | Destinataire (obligatoire en prod)         |
| `TURNSTILE_ENABLED`         | `false`              | Turnstile désactivé en dev/test (côté API — cf. ADR-008) |
| `TURNSTILE_SECRET`          | *vide*               | Requis si `TURNSTILE_ENABLED=true`         |
| `CONTACT_RATE_LIMIT`        | `5`                  | Jetons/fenêtre                             |
| `CONTACT_RATE_INTERVAL`     | `10 minutes`         | Fenêtre du bucket                          |
| `CONTACT_ORIGIN_ALLOWLIST`  | `http://localhost:3001` | Origins autorisés                       |
| `TRUSTED_PROXIES`           | `127.0.0.1,REMOTE_ADDR` | Caddy en frontal                       |
| `DATABASE_URL`              | `postgresql://devzair:devzair_local_password@postgres:5432/devzair?serverVersion=17&charset=utf8` | DSN Doctrine Phase 8A (jamais publié sur l'hôte) |

**Fail-safe** : `TURNSTILE_ENABLED=true` sans `TURNSTILE_SECRET` → boot
en exception. Documenté dans ADR-007.

**Deux flags Turnstile alignés** : `TURNSTILE_ENABLED` (API, ici) et
`NUXT_PUBLIC_TURNSTILE_ENABLED` (front, dans `apps/web`) doivent être
identiques — voir ADR-008. Une divergence produit un rejet systématique
ou charge un script Cloudflare pour rien.

## Administration (Phases 8C1 + 8C3)

Rendu SSR Symfony/Twig sous `/admin/**`, same-origin, sans surface JS
(CSP `default-src 'none'; script-src 'none'; style-src 'self'`). Voir
ADR-012 pour le socle 8C1 et DEC-087..091 dans `docs/10-TRACKING.md`
pour les décisions structurantes 8C3.

Routes du socle 8C1 :

- `GET /admin/login` et `POST /admin/login` (formulaire, CSRF
  `authenticate`, `post_only: true`, message d'erreur générique
  « Identifiants invalides »).
- `GET /admin` (dashboard = profil + logout ; `#[IsGranted('ROLE_ADMIN')]`).
- `POST /admin/logout` (CSRF `logout`, `invalidate_session: true`).
- `GET /admin/assets/*` (CSS statique — `PUBLIC_ACCESS`).

Routes de l'IHM éditoriale 8C3 (`#[IsGranted('ROLE_ADMIN')]`,
PRG systématique, `Cache-Control: private, no-store, no-cache,
must-revalidate` sur toutes les réponses) :

- `GET /admin/articles` — liste paginée, filtre `?status=draft|published|archived`.
- `GET /admin/articles/new` et `POST /admin/articles/new` — création
  brouillon (pipeline atomique, interception
  `UniqueConstraintViolationException` → `ArticleSlugAlreadyExistsException`
  cf. DEC-091).
- `GET /admin/articles/{id}/edit` et `POST /admin/articles/{id}/edit` —
  édition draft-only ; slug **absent du payload** (immuable, cf. DEC-091).
- `POST /admin/articles/{id}/publish` — via
  `PublishDraftArticleHandler` distinct du chemin CLI (cf. DEC-089).
- `POST /admin/articles/{id}/archive` — via `ArchiveArticleHandler`
  (8C2, idempotent).
- `POST /admin/articles/{id}/restore` — via `RestoreArticleHandler`
  (8C2, `Archived → Draft`, refuse `Published → *`).

Toutes les mutations utilisent un token CSRF dédié par action
(`publish|archive|restore-{uuid}`) et passent par `AdminActionRateLimiter`
(deux `RateLimiterFactory` : `admin_write` 30/min, `admin_publish`
10/min — indexés par UUID admin, cf. DEC-088). 429 avec `Retry-After`
+ template `rate_limited.html.twig` si `!isAccepted()`.

Session cookie dédié `DZ_ADMIN_SESSID` (HttpOnly, SameSite=Lax,
Secure=auto), lifetime piloté par `ADMIN_SESSION_LIFETIME` (défaut
28 800 s), stockage filesystem local. Throttling `login_throttling`
5 tentatives / 15 minutes (deux fenêtres `_login_local_admin` +
`_login_global_admin`). Password hashing Argon2id `auto` en prod.

Audit — canal Monolog `admin`, 8 événements éditoriaux 8C3 :
`admin.article.created` / `.updated` / `.update_noop` / `.published`
/ `.archived` / `.restored` / `.action_failed` / `.rate_limited`.
Seul identifiant admin loggé = UUID (jamais email, jamais displayName,
jamais titre ni Markdown ni HTML rendu).

Commandes CLI de gestion des comptes (mot de passe accepté uniquement
via `--password-stdin` ou `askHidden` interactif — jamais
`--password=xxx`) :

- `bin/console app:admin:create-user --email=... --display-name=...
  --password-stdin`
- `bin/console app:admin:reset-password --email=... --password-stdin`
- `bin/console app:admin:disable --email=...` (idempotent)

Correctif racine `apps/api/public/index.php` livré 8C3 : sous
`PHP_SAPI === 'cli-server'`, tout fichier réel de `public/`
court-circuite le kernel via `return false` (`is_file()` en garde
stricte + extraction chemin via `parse_url()` puis `urldecode()`).
Sans cette garde, le serveur PHP intégré routait `/admin/assets/*`
vers `index.php` et Symfony retournait du HTML — Chrome refusait la
CSS pour MIME strict. No-op en prod derrière Caddy/nginx/php-fpm. Cf.
DEC-087.

Phase 8C3 ne livre pas : upload d'images (report Phase 9),
invalidation coordonnée du cache Nitro sur publication (aucune purge
locale explicite — le cache Nitro respire par TTL, à traiter avec un
futur `ArticleUpdatedEvent`), prévisualisation authentifiée d'un
brouillon (Phase 8C4), recette OWASP finale et préparation prod
(Phase 8C4). Historique persistant des transitions, MFA, reset HTTP,
audit log persistant, Redis, multi-instance des sessions restent
différés (non planifiés dans 8C4 — à requalifier si le besoin
change).

## Ce qui n'est pas dans les Phases 6A / 6C / 8A / 8B1 / 8B2 / 8C1 / 8C2 / 8C3

- Endpoint HTTP JSON d'écriture des articles — refus explicite en
  Phase 8B1 (cf. DEC-074), pas de bascule en 8C3 (l'IHM Twig reste
  form-post + CSRF, aucun JSON admin).
- Rendu HTML côté HTTP des articles — livré en Phase 8B2 via
  `content_html` dans `ArticleDetailView` (calcul à la requête par
  `CommonMarkArticleRenderer`). Les listes exposent uniquement les
  résumés (`ArticleSummaryView`).
- Invalidation coordonnée du cache Nitro sur publication admin (bump
  ETag + purge Nitro locale) — le contrat public reste `v2`, l'ETag
  n'a pas changé en 8C3 ; à revoir avec un `ArticleUpdatedEvent` si
  le TTL Nitro devient insuffisant.
- Upload d'images éditoriales — report Phase 9 avec politique de
  stockage dédiée.
- Prévisualisation authentifiée d'un brouillon (aperçu Twig SSR
  utilisant `CommonMarkArticleRenderer`, headers `noindex` +
  `private, no-store`) et recette finale de sécurité (E2E complet,
  OWASP, préparation prod) — Phase 8C4.
- MFA, table d'audit persistante, reset HTTP, bascule session Redis,
  multi-instance sessions, rôles multiples — différés au-delà de
  8C4, à requalifier si le besoin change.
- Pages Nuxt `/ressources` et `/ressources/{slug}` — livrées en
  Phase 8B2 (SSR + cache Nitro + sitemap dynamique).
- Fixtures ou seeds d'articles fictifs (règle 1 AGENTS.md — rien
  inventer). La base démarre vide, y compris en dev — un rédacteur
  autorisé doit fournir le Markdown puis exécuter
  `app:editorial:import` + `app:editorial:publish`. Un jeu de
  fixtures E2E scopé (`e2e-8b2-*`) est disponible pour Playwright
  via `scripts/e2e-fixtures.sh load|clear`.
- Queue asynchrone / worker Messenger (envoi Mailer resté synchrone —
  ré-évaluer si `contact.mailer_unavailable` devient régulier).
- Image Open Graph dynamique.

Ces sujets sont différés aux phases suivantes.
