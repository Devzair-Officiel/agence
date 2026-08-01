export default defineNuxtConfig({
  compatibilityDate: '2026-08-01',

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

  runtimeConfig: {
    public: {
      siteUrl: 'http://localhost:3000',
      apiBaseUrl: 'http://localhost:8080/api',
    },
  },
})
