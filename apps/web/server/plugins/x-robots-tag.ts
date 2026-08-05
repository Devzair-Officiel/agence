// X-Robots-Tag global via Nitro `beforeResponse`.
//
// Contexte : en Nitro production, les routes pré-rendues (Phase 5D+ : `/`,
// `/agence`, `/expertises/**`, `/contact`) sont servies par le handler statique
// interne (`nitropack/dist/runtime/internal/static.mjs`), qui est enregistré
// AVANT tout middleware côté utilisateur dans la pile h3. Une fois qu'il a
// résolu un fichier `.html` pré-rendu, il retourne son buffer et court-circuite
// la suite du pipeline : aucun handler suivant ne s'exécute. Résultat : ni
// `@nuxtjs/robots` (v5.x) ni un `server/middleware/` maison ne peuvent poser
// l'en-tête `X-Robots-Tag` sur ces réponses (DEV-048).
//
// La solution route-par-route via `routeRules.headers` était fonctionnelle du
// point de vue HTTP, mais `@nuxtjs/sitemap` inspecte les headers de chaque
// routeRule et écarte silencieusement toute URL dont le header contient
// `X-Robots-Tag: noindex` (`sitemap/nitro.js:70`). Le sitemap devenait vide.
//
// Le hook Nitro `beforeResponse` s'exécute pour *toutes* les réponses (y
// compris celles émises par le static handler) juste avant l'envoi au client.
// Il permet donc de poser l'en-tête sans passer par `routeRules`.

export default defineNitroPlugin((nitroApp) => {
  const config = useRuntimeConfig()
  if (config.public.siteIndexable) return
  nitroApp.hooks.hook('beforeResponse', (event) => {
    if (event.path.startsWith('/_nuxt/') || event.path.startsWith('/__')) return
    // Ne pas écraser un header déjà posé par `@nuxtjs/robots` (robots.txt,
    // sitemap.xml émettent leur propre variante sans `noarchive`).
    if (getResponseHeader(event, 'X-Robots-Tag')) return
    setResponseHeader(event, 'X-Robots-Tag', 'noindex, nofollow, noarchive')
  })
})
