import { site } from "./app/config/site"

// Configuration Nuxt du frontend Devzair.
//
// Politique d'indexation :
//   - `runtimeConfig.public.siteUrl` alimente usePageSeo (canonicals absolus)
//   - `runtimeConfig.public.siteIndexable` alimente @nuxtjs/robots
//   - `site.indexable` (nuxt-site-config, embarqué par @nuxtjs/robots) est
//     dérivé de la même variable d'env pour ne pas dédoubler la source de
//     vérité — un seul booléen contrôle toute la politique du site.
//
// Modules SEO retenus (voir docs/adr/004-modules-seo.md) :
//   - @nuxtjs/robots  → X-Robots-Tag global, meta robots, robots.txt dynamique
//   - @nuxtjs/sitemap → sitemap.xml généré à partir des routes Nuxt
//
// Schema.org est injecté à la main par app/composables/useSiteSchema.ts
// (2 types stables, pas de dépendance supplémentaire).

export default defineNuxtConfig({
  compatibilityDate: '2026-08-01',

  modules: [
    '@nuxt/eslint',
    '@nuxt/fonts',
    '@nuxtjs/robots',
    '@nuxtjs/sitemap',
  ],

  // Stratégie polices : @nuxt/fonts détecte les `font-family` déclarées dans
  // le CSS (cf. tokens.css / fonts.css), télécharge les fichiers via Google
  // Fonts ou Bunny côté build, les auto-héberge et injecte un fallback
  // métrique (size-adjust) pour limiter le CLS. Aucune requête runtime vers
  // un CDN tiers en production.
  fonts: {
    families: [
      { name: 'Schibsted Grotesk', weights: [600, 700], subsets: ['latin'] },
      { name: 'Hanken Grotesk', weights: [400, 600], subsets: ['latin'] },
      { name: 'Space Mono', weights: [700], subsets: ['latin'] },
    ],
    defaults: {
      fallbacks: {
        'sans-serif': ['Inter', 'ui-sans-serif', 'system-ui'],
        monospace: ['ui-monospace', 'SFMono-Regular', 'Menlo'],
      },
    },
  },

  devtools: {
    enabled: true,
  },

  typescript: {
    strict: true,
  },

  css: [
    '~/assets/css/tokens.css',
    '~/assets/css/reset.css',
    '~/assets/css/fonts.css',
    '~/assets/css/animations.css',
    '~/assets/css/global.css',
  ],

  app: {
    head: {
      htmlAttrs: {
        lang: site.language,
      },
      titleTemplate: site.titleTemplate,
      meta: [
        {
          name: 'viewport',
          content: 'width=device-width, initial-scale=1',
        },
      ],
    },
  },

  // Source de vérité runtime. Les valeurs sont surchargées par les variables
  // NUXT_PUBLIC_* au démarrage. Aucun secret dans .public.
  runtimeConfig: {
    public: {
      siteUrl: 'http://localhost:3001',
      siteIndexable: false,
      apiBaseUrl: '/api',
      // Clé publique Cloudflare Turnstile (site-key, non-secrète). Vide en
      // dev : le widget émet automatiquement un token factice `dev-noop` et
      // l'API Symfony l'accepte via `AlwaysAllowTurnstileVerifier`. En prod,
      // NUXT_PUBLIC_TURNSTILE_SITE_KEY doit être défini — cf. ADR-007.
      turnstileSiteKey: '',
      // Interrupteur explicite Turnstile côté front. Doit être aligné avec
      // TURNSTILE_ENABLED côté API. Quand `false` :
      //   - aucun script Cloudflare n'est chargé (aucune requête réseau
      //     vers challenges.cloudflare.com) ;
      //   - le widget émet immédiatement le token `dev-noop` accepté par
      //     l'API. Voir ADR-008.
      // Défaut sûr : désactivé.
      turnstileEnabled: false,
    },
  },

  // Configuration nuxt-site-config, consommée par @nuxtjs/robots et
  // @nuxtjs/sitemap. `url` et `indexable` sont surchargés par les mêmes
  // variables NUXT_PUBLIC_SITE_URL / NUXT_PUBLIC_SITE_INDEXABLE.
  site: {
    url: 'http://localhost:3001',
    name: site.name,
    description: site.description,
    defaultLocale: site.language,
    indexable: false,
  },

  robots: {
    // En mode `site.indexable=false`, @nuxtjs/robots impose automatiquement :
    //   - robots.txt : User-agent: * / Disallow: /
    //   - meta robots injecté en <head>
    // Le X-Robots-Tag HTTP est posé par `server/plugins/x-robots-tag.ts` via
    // le hook `beforeResponse` de Nitro : le middleware fourni par
    // @nuxtjs/robots v5.x n'est pas invoqué pour les routes pré-rendues
    // servies par le static handler (DEV-048).
    sitemap: '/sitemap.xml',
    // Politique documentée pour les robots d'IA :
    //   - OAI-SearchBot (recherche ChatGPT) : autorisé
    //   - GPTBot (entraînement)             : refusé
    // Ces règles ne s'appliquent qu'en mode indexable.
    groups: [
      { userAgent: ['OAI-SearchBot'], allow: ['/'] },
      { userAgent: ['GPTBot'], disallow: ['/'] },
    ],
  },

  sitemap: {
    // Ne pas inventer de lastmod pour les routes statiques : mieux vaut
    // omettre la valeur que fournir une date fabriquée.
    autoLastmod: false,
  },

  // DEV-048 — désactivation de `payloadExtraction`.
  //
  // Par défaut (Nuxt 4), Nitro extrait la donnée d'hydratation de chaque route
  // pré-rendue dans un `_payload.json` séparé, puis `NuxtLink` en pré-fetch
  // le contenu à l'apparition du lien (`prefetchOn: 'visibility'`). Pour un
  // site 100 % statique sans `useAsyncData` côté serveur, ces payloads sont
  // vides (`{"data":1,...}`) — aucun gain d'hydratation, uniquement des
  // requêtes réseau supplémentaires.
  //
  // Le pré-fetch de payload sous Nitro production déclenche par ailleurs deux
  // diagnostics Nuxt bruyants (`NUXT_E7001` / `NUXT_E7003`, cf. le pipeline
  // `_getPayloadURL` de `app/composables/payload.js`) qui n'apparaissent
  // jamais sous `nuxt dev`. Les tests E2E capturent ces erreurs dans le
  // scénario « menu mobile → CTA → /contact » et échouent.
  //
  // Désactiver `payloadExtraction` court-circuite `loadPayload` (retour
  // immédiat `null`) et supprime les payloads du build. C'est le fix produit
  // minimal : aucune donnée applicative perdue, aucun bruit dans la console,
  // et la navigation SPA continue de fonctionner via le composant préchargé.
  experimental: {
    payloadExtraction: false,
  },

  routeRules: {
    // Pré-rendu limité aux pages marketing réellement existantes.
    // Phase 5D : accueil `/` (8 sections stables, 100 % SSR, données typées
    // locales). Phase 7A : ajout de `/agence` (positionnement + valeurs) et
    // `/expertises` (vue d'ensemble des cinq pôles) — même contrat : contenu
    // 100 % local, aucune donnée dynamique, HTML statique livrable.
    // Phase 7B : ajout des cinq pages filles `/expertises/{slug}` — servies
    // par une route dynamique Nuxt (`pages/expertises/[slug].vue`) qui
    // résout le slug via `expertise-pages.ts` et retourne un 404 explicite
    // (`createError`) pour toute autre valeur. Les slugs sont énumérés ci-
    // dessous pour que Nitro pré-rende chaque page à la construction et les
    // inclue dans le sitemap.
    //
    // On n'attache PAS `headers['X-Robots-Tag']` ici : @nuxtjs/sitemap
    // inspecte les headers de chaque routeRule et écarte silencieusement
    // toute URL dont le header contient `noindex` (sitemap/nitro.js:70).
    // L'en-tête est posé côté runtime par `server/plugins/x-robots-tag.ts`.
    '/': { prerender: true },
    '/agence': { prerender: true },
    '/contact': { prerender: true },
    '/expertises': { prerender: true },
    '/expertises/concevoir': { prerender: true },
    '/expertises/construire': { prerender: true },
    '/expertises/valoriser': { prerender: true },
    '/expertises/visibilite': { prerender: true },
    '/expertises/faire-evoluer': { prerender: true },
  },
})
