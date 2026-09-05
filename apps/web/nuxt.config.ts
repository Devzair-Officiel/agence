import { caseStudies } from "./app/config/case-studies"
import { expertisePages } from "./app/config/expertise-pages"
import { robotsGroups } from "./app/config/robots"
import { servicePages } from "./app/config/service-pages"
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
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'icon', type: 'image/png', sizes: '32x32', href: '/favicon-32x32.png' },
        { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' },
      ],
    },
  },

  // Source de vérité runtime. Les valeurs sont surchargées par les variables
  // NUXT_PUBLIC_* au démarrage. Aucun secret dans .public.
  runtimeConfig: {
    // Base URL interne de l'API Symfony utilisée par Nitro en SSR.
    // Doit pointer directement le conteneur `api` (chemin réseau interne
    // Compose) — sans passer par Caddy. Convention Nuxt : la variable
    // d'environnement `NUXT_EDITORIAL_API_BASE_URL` override cette clé.
    // Le préfixe `/api` est retiré côté Caddy avant forwarding, donc les
    // routes Symfony vivent sous `/resources`, `/contact`… : la base URL
    // interne ne doit PAS inclure `/api`. Voir ADR-011.
    editorialApiBaseUrl: '',
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
    // Politique crawlers Phase 10B / DEC-096 : source unique dans
    // `app/config/robots.ts` (documentée et testée unitairement).
    groups: robotsGroups,
  },

  sitemap: {
    // Ne pas inventer de lastmod pour les routes statiques : mieux vaut
    // omettre la valeur que fournir une date fabriquée.
    autoLastmod: false,
    // Source dynamique pour les URLs éditoriales (`/ressources/{slug}`).
    // L'endpoint interne itère la liste paginée de l'API Symfony et
    // retourne un tableau enrichi de `lastmod`. Il refuse de renvoyer une
    // liste vide silencieuse en cas d'indisponibilité (503) — cf. ADR-011.
    sources: ['/__sitemap__/resources'],
    // Phase 10A2 : sans `prerender: true`, @nuxtjs/sitemap ne détecte plus
    // les cinq pages `/expertises/{slug}` (route dynamique). On les
    // énumère explicitement pour préserver leur présence dans le sitemap.
    // Filtré aux pages `published` — une page `planned` ne doit pas y
    // apparaître (règle 11 : pas de placeholder indexable).
    urls: [
      ...expertisePages
        .filter((page) => page.status === 'published')
        .map((page) => ({ loc: page.route })),
      // SEO-COM-1 : le hub /services est publié ; les huit pages filles restent
      // `planned` et sont exclues par le filtre ci-dessous.
      { loc: '/services' },
      ...servicePages
        .filter((page) => page.status === 'published')
        .map((page) => ({ loc: page.route })),
      { loc: '/estimer-mon-projet' },
      // SEO-COM-4 : hub réalisations + cinq pages détail publiées.
      { loc: '/realisations' },
      ...caseStudies
        .filter((cs) => cs.status === 'published')
        .map((cs) => ({ loc: cs.route })),
    ],
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
    // (`createError`) pour toute autre valeur.
    //
    // Phase 10A2 (DEC-095) : les pages `/expertises/{slug}` embarquent une
    // section « Ressources liées » qui interroge l'API éditoriale au SSR.
    // Sans mécanisme d'ISR (Incremental Static Regeneration) branché sur les
    // publications côté back-office, un pré-rendu figerait la liste au
    // moment du build : après publication d'un article, la page expertise
    // serait obsolète jusqu'au prochain déploiement. On bascule donc sur du
    // SSR à la volée, mitigé par un `Cache-Control` court côté client et
    // plus long côté reverse proxy (identique à `/api/resources`), et par
    // le cache Nitro `editorialCache` qui négocie l'ETag JSON avec Symfony.
    //
    // On n'attache PAS `headers['X-Robots-Tag']` ici : @nuxtjs/sitemap
    // inspecte les headers de chaque routeRule et écarte silencieusement
    // toute URL dont le header contient `noindex` (sitemap/nitro.js:70).
    // L'en-tête est posé côté runtime par `server/plugins/x-robots-tag.ts`.
    '/': { prerender: true },
    '/agence': { prerender: true },
    '/contact': { prerender: true },
    '/expertises': { prerender: true },
    '/expertises/**': {
      headers: { 'Cache-Control': 'public, max-age=60, s-maxage=300' },
    },
    // SEO-COM-1 : hub services pré-rendu (contenu 100 % local, aucune donnée
    // dynamique). Les pages filles `/services/**` restent en SSR à la volée
    // jusqu'à leur publication effective (SEO-COM-2 et suivants).
    '/services': { prerender: true },
    // SEO-COM-2 : première page fille publiée — contenu 100 % local.
    '/services/creation-site-internet': { prerender: true },
    // SEO-COM-5A : deux nouvelles pages filles publiées — contenu 100 % local.
    '/services/site-e-commerce': { prerender: true },
    '/services/application-web-metier': { prerender: true },
    // SEO-COM-5B : deux nouvelles pages filles publiées — contenu 100 % local.
    '/services/design-ui-ux-identite-visuelle': { prerender: true },
    '/services/photographie-creation-contenu': { prerender: true },
    // SEO-COM-5C : trois dernières pages filles publiées — contenu 100 % local.
    '/services/seo-referencement-naturel': { prerender: true },
    '/services/visibilite-locale': { prerender: true },
    '/services/maintenance-accompagnement': { prerender: true },
    // SEO-COM-4 : hub réalisations + quatre pages détail publiées.
    // Contenu 100 % local (config case-studies.ts) — aucune donnée dynamique.
    // Haramain Prestige reste en `planned` (autorisation de publication client
    // non confirmée — voir case-studies.ts).
    '/realisations': { prerender: true },
    '/realisations/kitchen-meat': { prerender: true },
    '/realisations/nidemiel': { prerender: true },
    '/realisations/mizan': { prerender: true },
    '/realisations/al-mumayiz': { prerender: true },
  },
})
