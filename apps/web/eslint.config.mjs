// @ts-check
import withNuxt from './.nuxt/eslint.config.mjs'

// Configuration ESLint (Flat Config) — Devzair, apps/web
//
// Pilotée par @nuxt/eslint : la base Nuxt/Vue/TS est fournie par
// `./.nuxt/eslint.config.mjs` (généré via `nuxt prepare`).
// On ajoute nos propres surcouches ci-dessous. On garde le style minimal :
// pas de règles cosmétiques (`stylistic` désactivé côté module), l'objectif
// est la correction et la sûreté, pas la mise en forme.

export default withNuxt([
  {
    name: 'devzair/ignores',
    ignores: [
      '.nuxt/**',
      '.output/**',
      '.data/**',
      '.nitro/**',
      '.cache/**',
      'coverage/**',
      'dist/**',
      'node_modules/**',
      'playwright-report/**',
      'test-results/**',
    ],
  },
  {
    name: 'devzair/rules',
    rules: {
      // On refuse tout `any` explicite (cf. AGENTS.md règle 4).
      '@typescript-eslint/no-explicit-any': 'error',
      // Les imports/vars inutiles sont des bugs latents.
      '@typescript-eslint/no-unused-vars': [
        'error',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^_',
          caughtErrorsIgnorePattern: '^_',
        },
      ],
      // Vue : on tolère les composants mono-mot pour nos pages Nuxt.
      'vue/multi-word-component-names': 'off',
    },
  },
])
