# apps/web — Frontend Nuxt 4 (Devzair)

Frontend du site public Devzair. Nuxt 4, Vue 3, TypeScript strict.

Le mode de développement officiel est Docker Compose : voir le `README.md`
racine. Cette page documente le fonctionnement interne du package et les
options de développement en dehors de Docker.

## Stack

- Nuxt 4 (SSR par défaut).
- Vue 3, TypeScript strict.
- Tests unitaires : Vitest 3 + @vue/test-utils + happy-dom.
- Tests E2E : Playwright (un smoke test à ce jour).
- Lint : `@nuxt/eslint` (Flat Config).
- Polices auto-hébergées : `@nuxt/fonts`.

## Installation locale (hors Docker)

Node 24 recommandé (identique à l'image Docker).

```bash
npm ci
npm run dev            # http://localhost:3000
```

## Scripts

| Commande             | Rôle                                              |
|----------------------|---------------------------------------------------|
| `npm run dev`        | Serveur Nuxt en mode HMR                          |
| `npm run build`      | Build production                                  |
| `npm run preview`    | Sert le build production localement               |
| `npm run generate`   | Génération statique (pré-rendu)                   |
| `npm run lint`       | ESLint (Flat Config, `@nuxt/eslint`)              |
| `npm run lint:fix`   | ESLint avec autofix                               |
| `npm run typecheck`  | vue-tsc en mode strict                            |
| `npm run test`       | Vitest, un seul run                               |
| `npm run test:watch` | Vitest en mode watch                              |
| `npm run test:e2e`   | Playwright (nécessite les navigateurs, voir plus bas) |
| `npm run quality`    | lint → typecheck → tests unitaires → build        |

## Tests E2E (Playwright)

Le package `@playwright/test` est installé, mais les navigateurs (Chromium,
Firefox, WebKit) doivent être téléchargés séparément une première fois :

```bash
npx playwright install --with-deps chromium
npm run test:e2e
```

Le smoke test unique (`test/e2e/design-preview.spec.ts`) ouvre
`/design-preview` et vérifie qu'aucune erreur console ni avertissement
vue-router n'apparaît. La CI n'exécute pas encore ce test : il servira de
base pour la Phase 3.

## Structure

```
app/
  assets/css/          Tokens, reset, animations, styles globaux, polices
  components/
    base/              Composants présentiels (BaseButton, BaseContainer…)
    layout/            En-tête, pied, navigation mobile
  pages/               Routes Nuxt (index placeholder + design-preview)
public/                Fichiers servis tels quels (dont favicon)
test/                  Tests unitaires Vitest
  e2e/                 Tests Playwright (exclus de Vitest)
Dockerfile.dev         Image Node 24 Alpine, npm ci au build
```

## Docker

L'image de dev est décrite par `Dockerfile.dev` à la racine du package.
`compose.yaml` (racine du monorepo) l'utilise via un bind mount de
`./apps/web` pour le HMR, et deux volumes nommés pour `node_modules`
(persistés depuis l'image) et `.nuxt` (cache).
