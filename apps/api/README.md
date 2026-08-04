# apps/api — Backend Symfony 7.4 LTS (Phases 6A / 6C / 8A)

API HTTP de l'agence Devzair.

- Domaine `Contact` (Phases 6A + 6C) : `POST /api/contact` (soumission du
  formulaire) + `GET /api/health` (santé) + commande CLI de diagnostic
  `bin/console app:contact:check`. Aucun stockage, envoi email
  synchrone.
- Domaine `Editorial` (Phase 8A) : `GET /api/resources` (liste paginée
  des articles publiés) + `GET /api/resources/{slug}` (article publié
  par slug). Persistance PostgreSQL 17-alpine via Doctrine ORM 3,
  lecture publique uniquement — l'écriture, le renderer markdown et
  l'administration authentifiée sont reportés aux Phases 8B / 8C.

Voir les décisions consignées :

- `docs/adr/ADR-006-runtime-symfony-caddy.md` — runtime PHP + reverse proxy.
- `docs/adr/ADR-007-endpoint-contact-securite.md` — sécurité endpoint.
- `docs/adr/ADR-008-mailer-ovhcloud-turnstile-optionnel.md` — transport
  SMTP OVHcloud, Turnstile facultatif (deux flags alignés) et
  réponse HTTP 503 `temporary_error` sur échec Mailer.
- `docs/adr/ADR-009-persistance-postgresql-editorial.md` — PostgreSQL 17-alpine
  verrouillée, Doctrine ORM 3 par attributs sur l'entité `Article`,
  UUID v7 direct, `expertise_ids` en `jsonb`, markdown stocké brut.
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
  Pas de Twig, pas d'admin en Phase 8A.

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
  Editorial/                             ← Phase 8A
    Domain/                              ← modèle métier, sans I/O
      Article.php                        ← agrégat + attributs Doctrine
      ArticleRepositoryInterface.php     ← port
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
        ArticleNotFoundException.php
    Application/                         ← cas d'usage (lecture Phase 8A)
      Query/
        ListPublishedArticles.php        ← paramètres validés
        ListPublishedArticlesHandler.php ← invoke → {items, pagination}
        GetPublishedArticle.php
        GetPublishedArticleHandler.php
      View/
        ArticleSummaryView.php           ← sans body_markdown / seo
        ArticleDetailView.php            ← payload complet
        PaginationView.php               ← fromCount(page, perPage, total)
    Infrastructure/
      Persistence/
        DoctrineArticleRepository.php    ← implémente le port, filtre Published
    Presentation/
      Http/
        ListPublishedArticlesController.php  ← GET /resources
        GetPublishedArticleController.php    ← GET /resources/{slug}
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
   `body_markdown` brut — le renderer HTML est reporté Phase 8B) et
   `Cache-Control: public, max-age=60, s-maxage=300`.

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
- `tests/Editorial/Support/*` — `FixedClock`, `InMemoryArticleRepository`,
  `ArticleBuilder` (patterns partagés, jamais rendus publics).

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

## Ce qui n'est pas dans les Phases 6A / 6C / 8A

- Endpoint d'écriture des articles (POST / PUT / DELETE) — Phase 8B.
- Renderer markdown côté serveur + sanitizer HTML — Phase 8B (le
  contenu est actuellement stocké et exposé brut).
- Cache HTTP fin (ETag / Last-Modified) sur les lectures — Phase 8B.
- Back-office authentifié (édition, journal de publication) — Phase 8C.
- Pages Nuxt `/ressources` et `/ressources/{slug}` — Phase 9.
- Fixtures ou seeds d'articles fictifs (règle 1 AGENTS.md — rien
  inventer). La base démarre vide, y compris en dev.
- Queue asynchrone / worker Messenger (envoi Mailer resté synchrone —
  ré-évaluer si `contact.mailer_unavailable` devient régulier).
- Image Open Graph dynamique.

Ces sujets sont différés aux phases suivantes.
