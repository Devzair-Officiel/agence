# Architecture et qualité du code

> Architecture Nuxt/Docker/Symfony et application pragmatique de SOLID, DRY et KISS.

## 16. Architecture technique confirmée

## 16.1 Architecture cible

```text
Internet
   |
   v
Caddy / reverse proxy
   |
   +--> /, /services/**, /ressources/**  --> Nuxt 4 / Nitro
   |
   +--> /api/**                         --> Symfony 7.4 LTS
                                                |
                                                v
                                           PostgreSQL
```

### Principes

- un seul domaine public ;
- Nuxt reste responsable du rendu des pages, de l’UX et des métadonnées ;
- Symfony devient responsable des données du blog, de la validation métier, de l’administration et des autorisations ;
- PostgreSQL stocke les articles, catégories, auteurs et données métier ;
- Caddy gère TLS, redirections, compression et routage ;
- le navigateur ne reçoit jamais de secret Symfony ;
- Nuxt peut utiliser son serveur Nitro comme BFF lorsque des secrets ou cookies doivent être protégés.

## 16.2 Structure du monorepo

```text
devzair/
├── apps/
│   ├── web/
│   │   ├── app/
│   │   │   ├── assets/
│   │   │   │   └── css/
│   │   │   ├── components/
│   │   │   │   ├── base/
│   │   │   │   ├── layout/
│   │   │   │   ├── sections/
│   │   │   │   └── features/
│   │   │   ├── composables/
│   │   │   ├── config/
│   │   │   ├── layouts/
│   │   │   ├── pages/
│   │   │   ├── repositories/
│   │   │   ├── services/
│   │   │   ├── types/
│   │   │   ├── utils/
│   │   │   ├── app.vue
│   │   │   └── error.vue
│   │   ├── server/
│   │   │   ├── api/
│   │   │   ├── middleware/
│   │   │   └── utils/
│   │   ├── shared/
│   │   │   ├── types/
│   │   │   └── utils/
│   │   ├── public/
│   │   ├── test/
│   │   ├── Dockerfile
│   │   ├── Dockerfile.dev
│   │   ├── nuxt.config.ts
│   │   └── package.json
│   └── api/                         # ajouté à la phase Symfony
├── infra/
│   └── caddy/
├── docs/
│   └── adr/
├── compose.yaml
├── compose.prod.yaml
├── .env.example
├── .gitignore
├── Makefile
├── README.md
└── DEVZAIR_REFERENTIEL_PROJET.md
```

### Usage des répertoires Nuxt

| Répertoire | Responsabilité |
|---|---|
| `app/pages` | Orchestration des pages, récupération initiale et métadonnées |
| `app/components/base` | Composants élémentaires du design system |
| `app/components/layout` | Header, footer, navigation et structures globales |
| `app/components/sections` | Sections éditoriales réutilisables |
| `app/components/features` | Composants liés à une fonctionnalité |
| `app/composables` | Adaptation réactive Vue/Nuxt de cas d’usage |
| `app/services` | Logique applicative et orchestration indépendante de l’affichage |
| `app/repositories` | Contrats et adaptateurs d’accès aux données |
| `app/config` | Navigation, identité du site et configuration statique |
| `app/types` | Types propres à l’application frontend |
| `app/utils` | Fonctions pures sans état |
| `server/api` | Endpoints Nitro publics ou BFF |
| `server/utils` | Logique exclusivement serveur |
| `shared` | Types et fonctions réellement utilisables côté application et serveur |
| `public` | Fichiers servis tels quels |
| `test` | Tests unitaires, intégration et E2E |

Ne pas créer `layers/` pendant le MVP. Les layers deviennent pertinents seulement lorsqu’un domaine ou un design system doit être isolé ou partagé.

## 16.3 Application pragmatique de SOLID

### S — Responsabilité unique

- une page assemble les sections et définit son SEO ;
- un composant de présentation affiche des données reçues ;
- un composable gère un état ou un cas d’usage lié à Vue ;
- un service orchestre une action métier ;
- un repository accède à une source de données ;
- un utilitaire réalise une transformation pure.

Signaux d’alerte :

- composant dépassant plusieurs responsabilités ;
- appel API, validation, analytics et rendu dans le même fichier ;
- fichier générique nommé `helpers.ts` contenant des fonctions sans rapport ;
- page contenant tout le contenu, la logique et le style du projet.

### O — Ouvert à l’extension, fermé aux modifications inutiles

- utiliser des props typées et des slots pour les variantes réelles ;
- préférer la composition aux grands blocs conditionnels ;
- ajouter une variante sans casser les usages existants ;
- ne pas créer un composant universel configurable par des dizaines de props.

### L — Substitution

- un adaptateur de données doit respecter le contrat qu’il implémente ;
- une version mock, Symfony ou Nitro doit retourner les mêmes structures attendues ;
- les composants ne doivent pas dépendre des détails de la source.

### I — Ségrégation des interfaces

- créer de petits types ciblés ;
- ne pas transmettre un objet `Article` complet lorsqu’un composant n’utilise que `title`, `slug` et `excerpt` ;
- séparer les modèles de liste, détail et administration ;
- ne pas exposer les champs internes de Symfony au frontend.

### D — Inversion des dépendances

Le code métier dépend de contrats et non d’un appel `$fetch` dispersé.

```ts
// shared/types/article.ts
export interface ArticleSummary {
  slug: string
  title: string
  excerpt: string
  publishedAt: string
}

// app/repositories/ArticleRepository.ts
export interface ArticleRepository {
  findAll(): Promise<ArticleSummary[]>
  findBySlug(slug: string): Promise<ArticleDetail | null>
}
```

Un adaptateur Symfony implémentera ce contrat. Pour les premières pages statiques, ne pas créer de repository vide : ajouter cette abstraction au moment de l’intégration réelle du blog.

## 16.4 DRY, KISS et règle de trois

### À centraliser

- identité du site ;
- navigation ;
- coordonnées validées ;
- textes légaux récurrents ;
- génération des canonicals ;
- métadonnées communes ;
- boutons et champs de formulaire ;
- appels à l’API ;
- types d’articles ;
- formats de date ;
- gestion des erreurs ;
- événements analytics.

### À ne pas centraliser prématurément

Deux sections visuellement proches ne doivent pas forcément devenir un seul composant générique. Appliquer la règle suivante :

1. premier usage : implémentation claire ;
2. deuxième usage : observer les ressemblances et différences ;
3. troisième usage stable : extraire l’abstraction si elle réduit réellement le code et la complexité.

### Interdictions

- copier-coller un composant puis le modifier légèrement ;
- dupliquer nom, URL ou coordonnées de Devzair dans plusieurs pages ;
- dupliquer la logique de canonical ;
- appeler l’API Symfony depuis plusieurs composants avec des formats différents ;
- créer des composants `BaseSection` ou `UniversalCard` capables de tout faire ;
- stocker une logique métier dans le template Vue ;
- utiliser `any` pour contourner le typage.

## 16.5 Configuration Nuxt initiale

```ts
// apps/web/nuxt.config.ts
export default defineNuxtConfig({
  compatibilityDate: '2026-08-01',

  devtools: {
    enabled: true,
  },

  typescript: {
    strict: true,
    typeCheck: true,
  },

  runtimeConfig: {
    // Secrets exclusivement serveur à ajouter ici plus tard.
    public: {
      siteUrl: '',
      apiBaseUrl: '/api',
    },
  },

  app: {
    head: {
      htmlAttrs: {
        lang: 'fr',
      },
      meta: [
        {
          name: 'viewport',
          content: 'width=device-width, initial-scale=1',
        },
      ],
    },
  },

  routeRules: {
    '/': { prerender: true },
    '/agence': { prerender: true },
    '/methode': { prerender: true },
    '/services': { prerender: true },
    '/services/**': { prerender: true },
  },
})
```

Règles :

- ne jamais mettre un secret dans `runtimeConfig.public` ;
- déclarer les clés dans `runtimeConfig` avant de les surcharger par variables `NUXT_*` ;
- ne pas lire directement `process.env` dans les composants ;
- ne pas modifier les fichiers générés dans `.nuxt` ;
- conserver TypeScript strict ;
- ne pas désactiver SSR globalement ;
- ne pas activer une option expérimentale sans ADR.

## 16.6 Environnement Docker

### Objectif

Le conteneur de développement doit démarrer de façon reproductible sans lancer `npm install` à chaque démarrage.

### `apps/web/Dockerfile.dev`

```dockerfile
FROM node:24-alpine

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
```

### `compose.yaml`

```yaml
services:
  web:
    build:
      context: ./apps/web
      dockerfile: Dockerfile.dev
    working_dir: /app
    ports:
      - "3000:3000"
    volumes:
      - ./apps/web:/app
      - web_node_modules:/app/node_modules
    environment:
      NUXT_PUBLIC_SITE_URL: http://localhost:3000
      NUXT_PUBLIC_API_BASE_URL: /api
    restart: unless-stopped

volumes:
  web_node_modules:
```

### Commandes

```bash
docker compose build web
docker compose up -d
docker compose logs -f web
docker compose exec web npm run typecheck
docker compose exec web npm run lint
docker compose exec web npm run test
docker compose exec web npm run build
docker compose down
```

Après modification de `package.json` ou `package-lock.json` :

```bash
docker compose build --no-cache web
docker compose up -d
```

## 16.7 Outils de qualité à installer avant les pages

```bash
docker compose exec web npx nuxt module add eslint
docker compose exec web npm install --save-dev typescript vue-tsc
docker compose exec web npm install --save-dev \
  @nuxt/test-utils vitest @vue/test-utils happy-dom playwright
```

Scripts recommandés :

```json
{
  "scripts": {
    "dev": "nuxt dev",
    "build": "nuxt build",
    "preview": "nuxt preview",
    "postinstall": "nuxt prepare",
    "typecheck": "nuxt typecheck",
    "lint": "eslint .",
    "lint:fix": "eslint . --fix",
    "test": "vitest run",
    "test:watch": "vitest",
    "test:e2e": "playwright test"
  }
}
```

Chaque installation doit être suivie de :

```bash
docker compose exec web npm run typecheck
docker compose exec web npm run lint
docker compose exec web npm run build
```

## 16.8 Flux futur entre Nuxt et Symfony

### Lecture publique

```text
Page Nuxt
  -> composable
      -> service
          -> ArticleRepository
              -> adaptateur Symfony
                  -> /api/articles
```

### Écriture et administration

- les opérations d’administration sont réalisées dans une interface protégée ;
- Symfony valide toutes les données ;
- les autorisations sont contrôlées côté Symfony ;
- un middleware de route Nuxt ne constitue jamais une sécurité suffisante ;
- les tokens sensibles ne sont pas stockés dans `localStorage` ;
- les secrets et appels privilégiés passent côté serveur.

### Contenu HTML du blog

Éviter `v-html`. Préférer :

1. un contenu structuré en blocs rendus par composants contrôlés ;
2. ou un HTML assaini côté Symfony avec une politique stricte ;
3. puis une défense complémentaire côté frontend si nécessaire.

Aucun HTML fourni par un utilisateur ne doit être rendu sans assainissement.

## 16.9 ADR obligatoires

- `ADR-001` — Nuxt 4, Vue et TypeScript strict ;
- `ADR-002` — Docker Compose et monorepo ;
- `ADR-003` — stratégie SSR/pré-rendu ;
- `ADR-004` — module SEO retenu ;
- `ADR-005` — design system ;
- `ADR-006` — Symfony 7.4 LTS et PostgreSQL ;
- `ADR-007` — contrat API du blog ;
- `ADR-008` — authentification de l’administration ;
- `ADR-009` — stratégie de cache et invalidation ;
- `ADR-010` — analytics et consentement.

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
