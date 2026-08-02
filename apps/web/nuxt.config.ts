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
    //   - X-Robots-Tag: noindex, nofollow, noarchive sur toutes les réponses HTML
    //   - meta robots injecté en <head>
    // Aucune page ne peut échapper à la politique globale.
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

  routeRules: {
    // Pré-rendu limité aux pages marketing réellement existantes.
    // L'accueil couvre huit sections stables (hero, constat, réponse, cinq
    // pôles, réalisations, méthode, promesses, CTA final), 100 % rendue côté
    // serveur à partir de données typées locales : elle peut être servie en
    // HTML statique. Les autres routes marketing seront ajoutées au fil des
    // Phases 6+.
    '/': { prerender: true },
  },
})
