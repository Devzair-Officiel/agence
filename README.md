# Devzair — Monorepo

Site public de l'agence digitale Devzair. Monorepo Git unique organisant un
frontend Nuxt 4 et, depuis la Phase 6A, un backend Symfony 7.4 LTS derrière
un reverse proxy Caddy. Depuis la Phase 8A, une base PostgreSQL 17-alpine
sert de persistance au domaine éditorial (interne, jamais publiée sur
l'hôte).

L'accueil publique une home d'agence one-pager : 8 sections éditoriales
(hero → constat → réponse → 5 pôles → réalisations → méthode → pourquoi
Devzair → CTA final `#contact`), sans page interne clonée, sans placeholder,
sans coordonnée fictive. Phase 5 (accueil complète) est close.

La Phase 6A ajoute un endpoint sécurisé `POST /api/contact` (Symfony) et un
health check `GET /api/health`. La Phase 6B branche le formulaire côté
navigateur dans la section `#contact`. La Phase 6C prépare l'activation
réelle du transport SMTP OVHcloud (`MAILER_DSN` seule variable d'entrée,
Turnstile facultatif via deux flags alignés `TURNSTILE_ENABLED` +
`NUXT_PUBLIC_TURNSTILE_ENABLED`, réponse HTTP 503 `temporary_error` sur
échec SMTP, commande de diagnostic `bin/console app:contact:check`).

La Phase 8A introduit le domaine éditorial `Article` (Symfony + Doctrine
ORM 3 + PostgreSQL 17-alpine) et l'API publique de lecture :
`GET /api/resources?page=…&per_page=…` (paginée) et
`GET /api/resources/{slug}` (détail).

La Phase 8B1 ajoute un pipeline d'import Markdown CLI (`app:editorial:import`
create-only + `app:editorial:publish` idempotent, aucune écriture HTTP,
aucun back-office), la validation stricte des contenus (YAML front matter,
AST CommonMark, refus HTML brut / URL non `[http, https, mailto, tel]` /
`publishedAt` futur) et un cache HTTP conditionnel sur `/api/resources` :
ETag faible + `Last-Modified` (détail uniquement) → `304 Not Modified`
avec `X-Request-Id` préservé.

La Phase 8B2 livre les pages Nuxt publiques `/ressources` (listing SSR
paginé) et `/ressources/{slug}` (détail Markdown rendu côté serveur par
`CommonMarkArticleRenderer` — le rendu reste gouverné par
`MarkdownSecurityPolicy`). Sitemap dynamique via
`server/routes/__sitemap__/resources.ts` (retourne 503 si l'API
éditoriale est indisponible — refus du partiel). Un jeu de fixtures E2E
scopé dev/test uniquement (`bin/console app:editorial:e2e-fixtures
<load|clear>`, préfixe `e2e-8b2-*`) permet à Playwright de valider le
parcours via Caddy (`PLAYWRIGHT_BASE_URL=http://localhost:3001`) sans
aucune dépendance à `docker.sock` — voir `docs/08-ROADMAP.md`,
`docs/adr/ADR-008-mailer-ovhcloud-turnstile-optionnel.md`,
`docs/adr/ADR-009-persistance-postgresql-editorial.md`,
`docs/adr/ADR-010-pipeline-markdown-editorial-cache-http.md`,
`docs/adr/ADR-011-ssr-nuxt-editorial-cache-nitro.md`
et `docs/checklists/PRODUCTION-CONTACT.md`.

## Structure

```
apps/
  web/                  Frontend Nuxt 4 + Vue 3 + TypeScript strict
  api/                  Backend Symfony 7.4 LTS (endpoints /api/contact, /api/resources + CLI éditoriale)
infra/
  caddy/                Reverse proxy Caddy (Caddyfile + Dockerfile)
docs/                   Documentation projet (à lire de façon sélective, cf. AGENTS.md)
docs/adr/               Décisions d'architecture consignées (ADR)
.github/workflows/      Contrôles qualité CI (GitHub Actions)
AGENTS.md               Règles communes aux agents (source de vérité)
CLAUDE.md               Compléments propres à Claude Code
compose.yaml            Orchestration Docker de développement (caddy + web + api + postgres)
```

## Prérequis

- Docker et Docker Compose (mode dev « officiel » du projet).
- Alternative locale possible : Node 24 et npm.

## Démarrer en 30 secondes (Docker Compose)

```bash
cp .env.example .env          # facultatif : personnaliser le port ou l'URL
docker compose up -d --build  # build des quatre services (caddy, web, api, postgres)
docker compose logs -f caddy  # suivi du reverse proxy public
```

Le site est disponible sur http://localhost:3001. Caddy route :

- `http://localhost:3001/`             → Nuxt (conteneur `web`, page d'accueil)
- `http://localhost:3001/ressources`   → Nuxt (listing SSR paginé des articles publiés)
- `http://localhost:3001/ressources/…` → Nuxt (détail SSR d'un article, HTML rendu depuis Markdown)
- `http://localhost:3001/api/*`        → Symfony (conteneur `api`, préfixe `/api` strippé)

Le service `postgres` (PostgreSQL 17-alpine) n'est jamais exposé sur
l'hôte : Symfony y accède via le réseau interne Compose.

Vérification rapide de l'API :

```bash
curl -s http://localhost:3001/api/health           # → {"status":"ok"}
curl -s "http://localhost:3001/api/resources?page=1&per_page=10"  # → {"items":[…], "pagination":{…}, "request_id":"…"}
curl -sI "http://localhost:3001/api/resources?page=1&per_page=10" \
  | grep -i -E '^(etag|cache-control|x-request-id)'
# → ETag: W/"…"   Cache-Control: public, max-age=60, s-maxage=300   X-Request-Id: 01…
```

Import CLI d'un article Markdown (Phase 8B1, aucune écriture HTTP) :

```bash
docker compose exec api php bin/console app:editorial:import chemin/article.md --dry-run
docker compose exec api php bin/console app:editorial:import chemin/article.md
docker compose exec api php bin/console app:editorial:publish mon-slug
# ou avec date explicite (refusée si future) :
docker compose exec api php bin/console app:editorial:publish mon-slug --published-at="2026-01-15T09:00:00+00:00"
```

Jeu de fixtures E2E Phase 8B2 (dev/test uniquement, préfixe `e2e-8b2-*`,
scopé par `#[When(env: 'dev'|'test')]` — jamais chargé en prod) :

```bash
scripts/e2e-fixtures.sh load    # purge + insertion du jeu déterministe (7 publiés + 1 brouillon + 1 archivé + 1 futur)
scripts/e2e-fixtures.sh clear   # purge seulement (DELETE ciblé, jamais TRUNCATE)
```

Purge : `DELETE FROM editorial_article WHERE slug LIKE 'e2e-8b2-%'`
uniquement (jamais `TRUNCATE`, jamais `DELETE` global). La commande
n'est enregistrée dans le container que sous les environnements `dev`
et `test` (`#[When]`) — en `prod`, elle est simplement absente de la
liste `bin/console`.

Le script encapsule `docker compose exec -T api bin/console
app:editorial:e2e-fixtures <action>` : Playwright ne parle jamais
directement à Docker (`PLAYWRIGHT_BASE_URL=http://localhost:3001`
appelle uniquement Caddy).

Appliquer les migrations Doctrine après un premier `docker compose up` :

```bash
docker compose exec api php bin/console doctrine:migrations:migrate --no-interaction
```

Pour arrêter :

```bash
docker compose down
```

Les dépendances Node sont installées **au build** de l'image `web`, les
dépendances Composer au build de l'image `api`. Si un `package-lock.json`
ou un `composer.json` change, rebuild explicite :

```bash
docker compose build web    # front
docker compose build api    # back
```

## Contrôles qualité

À exécuter dans le conteneur (ou en local si Node 24 est installé) :

```bash
docker compose exec web npm run lint         # ESLint (@nuxt/eslint)
docker compose exec web npm run typecheck    # vue-tsc, TypeScript strict
docker compose exec web npm run test         # Vitest (unitaires)
docker compose exec web npm run build        # build Nuxt production
docker compose exec web npm run quality      # enchaîne les quatre au-dessus
```

Le workflow GitHub Actions `.github/workflows/web-quality.yml` reproduit
ces contrôles sur chaque push et pull request touchant `apps/web/`. Il
comprend en parallèle un second job **`e2e`** qui exécute la suite
Playwright (Chromium) dans l'image officielle
`mcr.microsoft.com/playwright:v1.62.1-noble`.

Les tests E2E ne tournent pas dans le conteneur `web` (Alpine ne peut
pas lancer les binaires Chromium fournis par Playwright). Pour les
lancer localement, se référer à `apps/web/README.md` : soit sur la
machine hôte après `npx playwright install`, soit via `docker run` sur
l'image `mcr.microsoft.com/playwright:v1.62.1-noble`.

## Variables d'environnement

`.env.example` liste **toutes** les variables — publiques (Nuxt) et
backend (Symfony). Les variables `NUXT_PUBLIC_*` alimentent
`runtimeConfig.public` ; les autres alimentent la config Symfony.

Trois variables gouvernent la politique SEO :

- `NUXT_PUBLIC_SITE_URL` — base absolue pour canonicals, OG et sitemap.
- `NUXT_PUBLIC_SITE_INDEXABLE` — bascule d'indexation (défaut : `false`).
  La production doit forcer `true` ; la preprod reste bloquée par défaut.
- `NUXT_PUBLIC_API_BASE_URL` — base d'appel API (par défaut `/api`).

Côté API, la Phase 6A introduit :

- `MAILER_DSN` — défaut sûr `null://null` (aucun email envoyé) ;
- `CONTACT_FROM_EMAIL` / `CONTACT_FROM_NAME` — identité expéditeur ;
- `CONTACT_RECIPIENT` — destinataire du formulaire (obligatoire en prod) ;
- `TURNSTILE_ENABLED` / `TURNSTILE_SECRET` — vérification anti-bot ;
- `CONTACT_RATE_LIMIT` / `CONTACT_RATE_INTERVAL` — rate limit par IP ;
- `CONTACT_ORIGIN_ALLOWLIST` — CSRF stateless (voir ADR-007).

La Phase 8A ajoute la persistance :

- `POSTGRES_DB` / `POSTGRES_USER` / `POSTGRES_PASSWORD` — service Compose
  `postgres:17-alpine` (interne uniquement, cf. ADR-009) ;
- `DATABASE_URL` — DSN Doctrine (`postgresql://…?serverVersion=17&charset=utf8`),
  défaut pointant sur le service interne `postgres:5432`.

En production, la base est managée (OVH) et la version majeure de
PostgreSQL doit rester alignée sur celle de dev/CI (17) — toute évolution
majeure passe par une nouvelle ADR (cf. ADR-009).

La Phase 6C ajoute un jumeau front à `TURNSTILE_ENABLED` :
`NUXT_PUBLIC_TURNSTILE_ENABLED` (défaut `false`). Les deux flags
**doivent** rester alignés — voir ADR-008. Avant tout déploiement du
formulaire réel, exécuter dans l'environnement cible :

```bash
docker compose exec api php bin/console app:contact:check --env=prod
# → Configuration OK  (ou liste explicite d'erreurs bloquantes)
```

La sortie ne divulgue jamais le DSN complet, le secret Turnstile ni les
emails en clair : elle peut être collée dans un ticket d'ops. Séquence
complète de mise en production dans
`docs/checklists/PRODUCTION-CONTACT.md`.

Aucun secret ne doit être commité, ni ajouté à `runtimeConfig.public`.
Les vraies valeurs de production passent par un secret manager, jamais
par Git.

## Documentation projet

Voir `docs/README.md` pour l'index et la matrice de lecture sélective des
documents (référentiel, contenu, SEO, sécurité, architecture, roadmap,
tracking).
