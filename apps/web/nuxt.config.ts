export default defineNuxtConfig({
  compatibilityDate: '2026-08-01',

  modules: ['@nuxt/eslint', '@nuxt/fonts'],

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
        lang: 'fr',
      },
      titleTemplate: '%s | Devzair',
      meta: [
        {
          name: 'viewport',
          content: 'width=device-width, initial-scale=1',
        },
      ],
    },
  },

  // Les valeurs sont surchargées par NUXT_PUBLIC_* au runtime (cf. .env.example).
  // On garde des défauts explicites pour un dev sans .env, mais aucun secret
  // ne doit apparaître dans runtimeConfig.public.
  runtimeConfig: {
    public: {
      siteUrl: 'http://localhost:3000',
      apiBaseUrl: '/api',
    },
  },
})
