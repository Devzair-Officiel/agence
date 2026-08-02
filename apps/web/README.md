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
- SEO technique : `@nuxtjs/robots` v5.7 (indexation, robots.txt dynamique,
  X-Robots-Tag) et `@nuxtjs/sitemap` v7.6 (sitemap.xml). Schema.org injecté
  à la main par `useSiteSchema` (Organization + WebSite). Choix justifié
  dans [docs/adr/ADR-004-modules-seo.md](../../docs/adr/ADR-004-modules-seo.md).

## Variables d'environnement SEO

| Variable                       | Défaut                  | Effet                                                                 |
|--------------------------------|-------------------------|-----------------------------------------------------------------------|
| `NUXT_PUBLIC_SITE_URL`         | `http://localhost:3001` | Base absolue des canonicals, OG et sitemap. Jamais dérivée du header Host. |
| `NUXT_PUBLIC_SITE_INDEXABLE`   | `false` (safe)          | Bascule toute la politique d'indexation (robots, X-Robots-Tag, meta). |
| `NUXT_PUBLIC_API_BASE_URL`     | `/api`                  | Base des appels API front (préparation Symfony).                      |

Le défaut `false` garantit que la preprod ne peut pas devenir indexable
par simple oubli. La production doit forcer `NUXT_PUBLIC_SITE_INDEXABLE=true`
en même temps qu'un `NUXT_PUBLIC_SITE_URL` https public.

## Contrat SEO par page

Une page publie ses métadonnées via un unique composable :

```ts
usePageSeo({
  title: 'Nos services',
  description: 'Sites, applications et stratégie SEO/GEO.',
  path: '/services',
  // Optionnels
  image: '/og/services.png',       // ou URL absolue https://cdn…/x.png
  imageAlt: 'Illustration Devzair',
  type: 'article',                  // défaut 'website'
  noindex: true,                    // ou robots: 'noindex, nofollow'
})
```

Règles importantes :

- Une page `noindex` n'émet **pas** de canonical (éviter le signal ambigu).
- Le canonical est calculé à partir de `NUXT_PUBLIC_SITE_URL` + `path`
  (query et fragment retirés). Jamais depuis le header HTTP.
- Le graphe Schema.org Organization + WebSite est injecté une seule fois
  via `useSiteSchema()` dans `app/layouts/default.vue` — ne pas dupliquer
  au niveau des pages.

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

Neuf suites Playwright couvrent l’accueil `/` et la page interne
`/design-preview` :

| Fichier                                       | Ce qui est vérifié                                              |
|-----------------------------------------------|------------------------------------------------------------------|
| `test/e2e/home-hero.spec.ts`                  | Accueil `/` : HTTP 200 sans erreurs console, un seul H1 avec la phrase complète, introduction et deux CTA `#contact` / `#realisations`, SVG des cinq pôles (`role="img"`, `<title>`, 5 nœuds), SEO complet (title, description, canonical, OG, Twitter, `lang="fr"`), respect de `prefers-reduced-motion` (état final visible), Axe WCAG 2.2 AA sans violation `serious`/`critical`, aucun débordement horizontal aux breakpoints 390 / 768 / 1440. |
| `test/e2e/home-sections-primary.spec.ts`      | Ordre éditorial des 4 sections (hero → constat → réponse → expertises), un seul H1 + un H2 par section, 5 items dans chaque `<ol>` (problèmes, parcours) et 5 cartes pôles avec 3 services chacune, ancre `#expertises` atteignable, présence SSR des 5 libellés et longDescription, Axe WCAG 2.2 AA, `prefers-reduced-motion` (scroll-behavior `auto` sur le carrousel), responsive 320 / 390 / 768 / 1440 sans débordement horizontal. |
| `test/e2e/home-sections-secondary.spec.ts`    | Ordre éditorial des 7 sections (ajout de `home-case`, `home-process`, `home-trust`), 3 nouveaux H2 verbatim, section `#realisations` en variante honest-state sans lien ni bouton, `<ol>` de 6 étapes de méthode dans l'ordre `Découverte`…`Évolution`, `<ul>` de 5 promesses dans l'ordre attendu, CTA hero « Découvrir nos réalisations » qui scrolle vers `#realisations`, lien header « Réalisations » → `/#realisations`, présence SSR de 18 signatures éditoriales exactes, Axe WCAG 2.2 AA, `prefers-reduced-motion`, responsive 320 / 390 / 768 / 1024 / 1440 sans débordement horizontal. |
| `test/e2e/design-preview.spec.ts`             | H1 unique, `noindex, nofollow`, skip link, header + footer, absence de contact fictif, erreurs console. |
| `test/e2e/mobile-navigation.spec.ts`          | Cible tactile 44×44, `aria-expanded`, ouverture / focus / Escape / scroll-lock / focus trap Tab & Shift+Tab. |
| `test/e2e/keyboard-navigation.spec.ts`        | Skip link atteint en premier Tab, liens accessibles clavier, absence de faux boutons, anneau de focus visible. |
| `test/e2e/responsive.spec.ts`                 | Aucun débordement horizontal aux breakpoints 390 / 768 / 1440, header + main + footer visibles, texte ≥ 14 px. |
| `test/e2e/reduced-motion.spec.ts`             | `prefers-reduced-motion` neutralise les transitions et la nav mobile reste utilisable. |
| `test/e2e/accessibility.spec.ts`              | Scan Axe (WCAG 2.0/2.1/2.2 A + AA) — échoue sur toute violation `serious` ou `critical`. |

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
    home/              Sections de l'accueil (HomeHero, HomeEcosystemGraph, HomeProblems, HomeConnectedApproach, HomeExpertisePillars, HomeFeaturedCaseStudy, HomeProcess, HomeTrust)
    layout/            En-tête, pied, navigation mobile
  config/              Sources de vérité typées (site, navigation, expertise-pillars, project-process, trust-promises…)
  pages/               Routes Nuxt (index accueil + design-preview interne)
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
