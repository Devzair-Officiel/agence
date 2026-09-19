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
      // preload: true rétablit le préchargement supprimé par `subsets`.
      // styles: ['normal'] exclut les variantes italiques, non utilisées dans
      // le design system. Cela réduit le nombre de fichiers woff2 et garantit
      // que le `preload` cible Hanken Grotesk 400 normal (police du LCP) et
      // Schibsted Grotesk (titres above-fold), et non leurs variantes italiques.
      { name: 'Schibsted Grotesk', weights: [600, 700], styles: ['normal'], subsets: ['latin'], preload: true },
      { name: 'Hanken Grotesk', weights: [400, 600], styles: ['normal'], subsets: ['latin'], preload: true },
      { name: 'Space Mono', weights: [700], styles: ['normal'], subsets: ['latin'] },
    ],
    defaults: {
      fallbacks: {
        'sans-serif': ['Inter', 'ui-sans-serif', 'system-ui'],
        monospace: ['ui-monospace', 'SFMono-Regular', 'Menlo'],
      },
    },
  },

  devtools: {
    enabled: process.env.NODE_ENV !== 'production',
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
    '~/assets/css/primitives.css',
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
    // Pages légales exclues du sitemap : `noindex, follow` → aucune valeur
    // SEO, présence dans le sitemap serait contradictoire.
    exclude: ['/mentions-legales', '/politique-de-confidentialite'],
    // Phase 10A2 : sans `prerender: true`, @nuxtjs/sitemap ne détecte plus
    // les cinq pages `/expertises/{slug}` (route dynamique). On les
    // énumère explicitement pour préserver leur présence dans le sitemap.
    // Filtré aux pages `published` — une page `planned` ne doit pas y
    // apparaître (règle 11 : pas de placeholder indexable).
    urls: [
      ...expertisePages
        .filter((page) => page.status === 'published')
        .map((page) => ({ loc: page.route })),
      // SEO-COM-1 : le hub /services est publié. Les huit pages filles sont
      // incluses via le filtre `published` ci-dessous (toutes publiées en SEO-COM-5).
      { loc: '/services' },
      ...servicePages
        .filter((page) => page.status === 'published')
        .map((page) => ({ loc: page.route })),
      { loc: '/estimer-mon-projet' },
      // SEO-COM-4 : hub réalisations + quatre pages détail publiées.
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

  nitro: {
    // Génère des variantes .gz et .br de tous les assets statiques (_nuxt/**).
    // Nitro les sert automatiquement quand le browser envoie Accept-Encoding.
    // En prod réelle Caddy gère la compression ; ici cela permet de tester
    // les performances en standalone sans passer par le proxy.
    compressPublicAssets: { gzip: true, brotli: true },
  },

  routeRules: {
    // Assets versionnés (noms avec hash de contenu) — cache immuable 1 an.
    // Sûr car un rebuild génère de nouveaux noms dès qu'un fichier change.
    '/_nuxt/**': {
      headers: { 'Cache-Control': 'public, max-age=31536000, immutable' },
    },
    // Images de marque — noms stables, changements rares. 1 an sans `immutable`
    // (pas de hash dans le nom) : le proxy invalide si nécessaire, et un
    // ?v= suffira à contourner le cache navigateur en cas de mise à jour.
    '/brand/**': {
      headers: { 'Cache-Control': 'public, max-age=31536000' },
    },
    // Images portfolio — pas de hash de nom, mais changements rares.
    // 30 jours laissent le temps de re-télécharger si les variantes changent.
    '/portfolio/**': {
      headers: { 'Cache-Control': 'public, max-age=2592000' },
    },
    // Politique Cache-Control HTML — pages marketing prérendues :
    //
    //   max-age=0           → le navigateur ne garde PAS le HTML en cache local ;
    //                         cold browser cache garanti à chaque visite.
    //   s-maxage=86400      → Cloudflare (proxy CDN) cache le HTML 24 h ;
    //                         TTFB cold ≈ 20–50 ms depuis l'edge.
    //   stale-while-revalidate=60 → Cloudflare continue de servir le cache
    //                         pendant 60 s après expiration le temps de refetch.
    //
    // Seules les pages marketing entrent dans cette politique (contenu stable,
    // données 100 % locales). Les routes avec données dynamiques ou utilisateur
    // conservent leur comportement propre (SWR Nitro, no-cache, etc.).
    //
    // Note : X-Robots-Tag n'est pas posé ici (@nuxtjs/sitemap écarte toute route
    // portant `noindex` dans ses headers routeRules — cf. sitemap/nitro.js:70).
    // L'en-tête est posé par server/plugins/x-robots-tag.ts.
    //
    // Phase 10A2 (DEC-095) : /expertises/{slug} font des appels API SSR
    // (ExpertiseRelatedResources). Pas de prerender ; Cache-Control court
    // côté client + s-maxage 5 min côté proxy.
    //
    // SWR 60s sur /ressources : rendu SSR en mémoire Nitro, clé = URL complète.
    '/ressources': { swr: 60 },
    '/': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/agence': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/contact': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    // Contenu 100 % local (config estimator-*.ts) — l'API n'est appelée
    // qu'en client après soumission du formulaire, jamais au SSR.
    // Prérendu : TTFB 1ms (vs 11–90ms SSR froid) + HTML compressé statique.
    '/estimer-mon-projet': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/expertises': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/expertises/**': {
      headers: { 'Cache-Control': 'public, max-age=0, s-maxage=300, stale-while-revalidate=60' },
    },
    // SEO-COM-1..5 : pages services — contenu 100 % local.
    '/services': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/services/creation-site-internet': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/services/site-e-commerce': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/services/application-web-metier': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/services/design-ui-ux-identite-visuelle': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/services/photographie-creation-contenu': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/services/seo-referencement-naturel': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/services/visibilite-locale': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/services/maintenance-accompagnement': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    // SEO-COM-4 : hub réalisations + quatre pages détail publiées.
    // Haramain Prestige reste en `planned` (autorisation client non confirmée).
    '/realisations': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/realisations/kitchen-meat': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/realisations/nidemiel': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/realisations/mizan': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    '/realisations/al-mumayiz': { prerender: true, headers: { 'Cache-Control': 'public, max-age=0, s-maxage=86400, stale-while-revalidate=60' } },
    // Pages légales — non indexées via usePageSeo (noindex, follow).
    // PAS de noindex dans ces headers : @nuxtjs/sitemap écarte silencieusement
    // toute route portant noindex dans ses routeRules headers.
    '/mentions-legales': { prerender: true },
    '/politique-de-confidentialite': { prerender: true },
  },
})
