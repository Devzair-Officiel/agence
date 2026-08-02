# apps/web — Frontend Nuxt 4 (Devzair)

Frontend du site public Devzair. Nuxt 4, Vue 3, TypeScript strict.

Le mode de développement officiel est Docker Compose : voir le `README.md`
racine. Cette page documente le fonctionnement interne du package et les
options de développement en dehors de Docker.

## Stack

- Nuxt 4 (SSR par défaut).
- Vue 3, TypeScript strict.
- Tests unitaires : Vitest 3 + @vue/test-utils + happy-dom.
- Tests E2E : Playwright 1.62 + @axe-core/playwright 4.12 (Chromium seul).
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
| `npm run test:e2e:ui`| Playwright en mode UI interactif                  |
| `npm run quality`    | lint → typecheck → tests unitaires → build        |
| `npm run quality:full` | `quality` + Playwright E2E                      |

## Tests E2E (Playwright)

Six suites Playwright couvrent la page `/design-preview` :

| Fichier                                    | Ce qui est vérifié                                              |
|--------------------------------------------|------------------------------------------------------------------|
| `test/e2e/design-preview.spec.ts`          | H1 unique, `noindex, nofollow`, skip link, header + footer, absence de contact fictif, erreurs console. |
| `test/e2e/mobile-navigation.spec.ts`       | Cible tactile 44×44, `aria-expanded`, ouverture / focus / Escape / scroll-lock / focus trap Tab & Shift+Tab. |
| `test/e2e/keyboard-navigation.spec.ts`     | Skip link atteint en premier Tab, liens accessibles clavier, absence de faux boutons, anneau de focus visible. |
| `test/e2e/responsive.spec.ts`              | Aucun débordement horizontal aux breakpoints 390 / 768 / 1440, header + main + footer visibles, texte ≥ 14 px. |
| `test/e2e/reduced-motion.spec.ts`          | `prefers-reduced-motion` neutralise les transitions et la nav mobile reste utilisable. |
| `test/e2e/accessibility.spec.ts`           | Scan Axe (WCAG 2.0/2.1/2.2 A + AA) — échoue sur toute violation `serious` ou `critical`. |

### Stratégie d'exécution

Le CI exécute ces tests dans l'image officielle
`mcr.microsoft.com/playwright:v1.62.1-noble` (Chromium + libs déjà
présents). L'image de dev `apps/web/Dockerfile.dev` reste sur Node 24
Alpine et n'embarque pas Chromium.

**Localement, sur la machine hôte** (Node 24 disponible) :

```bash
cd apps/web
npm ci
npx playwright install --with-deps chromium
npm run test:e2e
```

**Localement, en Docker** (le conteneur `web` tourne, mais Alpine ne
peut pas lancer Chromium) : réutiliser la même image que le CI et cibler
le serveur exposé sur `localhost:3001`.

```bash
docker compose up -d web
docker run --rm --network host \
  -v "$(pwd)/apps/web":/work -w /work \
  -e PLAYWRIGHT_BASE_URL=http://localhost:3001 \
  mcr.microsoft.com/playwright:v1.62.1-noble \
  bash -c "npm ci && npm run test:e2e"
```

La variable `PLAYWRIGHT_BASE_URL` désactive le `webServer` interne de
`playwright.config.ts` et permet de pointer vers n'importe quelle URL
existante (staging, preview…).

### Ce que les tests ne remplacent pas

Les contrôles manuels documentés dans `docs/02-DESIGN-ACCESSIBILITY.md`
restent obligatoires : zoom navigateur 200 %, parcours clavier complet
sur clavier physique, inspection ARIA au lecteur d'écran, essai sans JS,
essai sans police téléchargée, largeur 320 px, `prefers-reduced-motion`
au niveau OS.

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
