/**
 * Source dynamique du sitemap pour les URLs éditoriales.
 *
 * Enregistrée dans `nuxt.config.ts` → `sitemap.sources`. `@nuxtjs/sitemap`
 * appelle cet endpoint pendant la construction du sitemap et fusionne son
 * retour avec les autres sources (routes statiques déclarées par `routeRules`).
 *
 * Format retourné : tableau de `SitemapUrlInput` (`loc` obligatoire, tout
 * autre champ optionnel : `lastmod`, `changefreq`, `priority`…). Ici on
 * fournit `loc` + `lastmod` (dérivé de `updatedAt` fourni par l'API).
 *
 * Contrat d'erreur (correction obligatoire 5 du brief Phase 8B2) :
 *   - API disponible + liste vide            → `[]` (sitemap sans URL
 *                                              ressource, ce qui est
 *                                              honnête : rien à indexer) ;
 *   - API disponible + liste non vide        → URLs enrichies avec lastmod ;
 *   - API indisponible / payload invalide    → `createError(503)` : on refuse
 *                                              de générer un sitemap
 *                                              partiel ou silencieusement
 *                                              vide. Le sitemap n'est pas
 *                                              livré tant que la source
 *                                              n'est pas fiable.
 *
 * Pas de préfixe `/api/*` : Caddy routerait alors vers Symfony. Le préfixe
 * `/__sitemap__/*` est réservé à Nitro.
 *
 * L'endpoint pagine côté serveur en interne — `@nuxtjs/sitemap` ne veut pas
 * savoir combien de pages existent. Bornage à 50 items/page (max autorisé
 * par le validateur Symfony `/resources` — voir
 * `ListPublishedArticlesController`) ; boucle jusqu'à collecte complète.
 */

import type { ArticleListResult, ArticleSummary } from "~/types/editorial"
import { editorialCache } from "~~/server/utils/editorial-runtime"

interface SitemapEntry {
  loc: string
  lastmod?: string
}

const PAGE_SIZE = 50
const MAX_PAGES = 200 // bornage défensif : 10 000 articles maximum.

export default defineEventHandler(async (): Promise<SitemapEntry[]> => {
  const cache = editorialCache()

  const entries: SitemapEntry[] = []
  let page = 1
  let totalPages = 1

  while (page <= totalPages && page <= MAX_PAGES) {
    const result = await cache.list(page, PAGE_SIZE)

    if (result.status !== "ok") {
      // 404 sur pagination hors bornes = comportement anormal ici (on suit
      // le total renvoyé par la première page). On considère toute non-ok
      // comme 503 pour ne PAS livrer un sitemap partiel.
      throw createError({
        statusCode: 503,
        statusMessage:
          "Sitemap ressources indisponible — source éditoriale non fiable.",
      })
    }

    appendEntries(entries, result.data.items)
    totalPages = result.data.pagination.totalPages

    if (result.data.items.length === 0) break
    page += 1
  }

  return entries
})

function appendEntries(
  target: SitemapEntry[],
  items: readonly ArticleSummary[] | ArticleListResult["items"],
): void {
  for (const article of items) {
    target.push({
      loc: `/ressources/${article.slug}`,
      lastmod: article.updatedAt,
    })
  }
}
