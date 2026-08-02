# Devzair — Monorepo

Site public de l'agence digitale Devzair. Monorepo Git unique organisant, à
terme, un frontend Nuxt 4 et un backend Symfony 7.4. À ce jour, seul le
frontend existe.

L'accueil publique une home d'agence one-pager : 8 sections éditoriales
(hero → constat → réponse → 5 pôles → réalisations → méthode → pourquoi
Devzair → CTA final `#contact`), sans page interne clonée, sans placeholder,
sans coordonnée fictive. Phase 5 (accueil complète) est close.

## Structure

```
apps/
  web/                  Frontend Nuxt 4 + Vue 3 + TypeScript strict
docs/                   Documentation projet (à lire de façon sélective, cf. AGENTS.md)
.github/workflows/      Contrôles qualité CI (GitHub Actions)
AGENTS.md               Règles communes aux agents (source de vérité)
CLAUDE.md               Compléments propres à Claude Code
compose.yaml            Orchestration Docker de développement
```

## Prérequis

- Docker et Docker Compose (mode dev « officiel » du projet).
- Alternative locale possible : Node 24 et npm.

## Démarrer en 30 secondes (Docker Compose)

```bash
cp .env.example .env          # facultatif : personnaliser le port ou l'URL
docker compose up -d          # build de l'image `apps/web/Dockerfile.dev` puis démarrage
docker compose logs -f web    # suivi du serveur Nuxt
```

Nuxt est disponible sur http://localhost:3001 (le port hôte est mappé sur le
port 3000 du conteneur).

Pour arrêter :

```bash
docker compose down
```

Les dépendances sont installées **au build** (`npm ci` dans le Dockerfile).
Elles ne sont pas réinstallées à chaque démarrage. Si `package-lock.json`
change, rebuild explicite :

```bash
docker compose build web
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

`.env.example` liste les variables publiques attendues. Elles sont
préfixées `NUXT_PUBLIC_*` et surchargent automatiquement
`runtimeConfig.public` dans `apps/web/nuxt.config.ts`.

Trois variables gouvernent la politique SEO :

- `NUXT_PUBLIC_SITE_URL` — base absolue pour canonicals, OG et sitemap.
- `NUXT_PUBLIC_SITE_INDEXABLE` — bascule d'indexation (défaut : `false`).
  La production doit forcer `true` ; la preprod reste bloquée par défaut.
- `NUXT_PUBLIC_API_BASE_URL` — base d'appel API (par défaut `/api`).

Aucun secret ne doit être commité, ni ajouté à `runtimeConfig.public`.

## Documentation projet

Voir `docs/README.md` pour l'index et la matrice de lecture sélective des
documents (référentiel, contenu, SEO, sécurité, architecture, roadmap,
tracking).
