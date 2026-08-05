/**
 * Cache serveur des représentations éditoriales, entre Nitro et Symfony.
 *
 * Motivation (ADR-011) :
 *   - Le validateur HTTP `If-None-Match` envoyé par le navigateur concerne
 *     la représentation HTML de la page Nuxt. Le validateur ETag renvoyé
 *     par Symfony concerne la représentation JSON de la ressource. Ces
 *     deux représentations ne sont pas identiques : on ne peut donc pas
 *     relayer aveuglément l'un pour l'autre.
 *   - En revanche, Nitro peut maintenir un cache LOCAL des payloads JSON
 *     validés, et négocier avec Symfony en utilisant l'ETag stocké
 *     localement. Sur 304, on réutilise le payload local ; sur 200, on
 *     remplace le cache.
 *
 * Politique :
 *   - Ne pas cacher : 400, 404, 5xx, payload invalide.
 *   - Ne pas calculer d'ETag Nuxt pour les données JSON amont.
 *   - Ne pas propager le `If-None-Match` du navigateur vers Symfony.
 *
 * Le stockage utilise `useStorage()` (Nitro) — en dev/test cela retombe
 * sur la mémoire, en prod on peut monter un backend persistant (Redis…)
 * sans changer ce code.
 */

import type { ArticleDetail, ArticleListResult } from "~/types/editorial"
import {
  createEditorialApi,
  type EditorialApiOptions,
  type EditorialResult,
} from "~/services/editorial-api"

interface CachedEntry<T> {
  data: T
  etag: string | null
  lastModified: string | null
  cachedAt: number
}

const STORAGE_NAMESPACE = "editorial"

function storage() {
  // Bind par appel : `useStorage` n'existe pas hors contexte Nitro.
  // Import synchrone (auto-imported en Nitro) — on assume un runtime Nitro.
  return useStorage(STORAGE_NAMESPACE)
}

function listKey(page: number, perPage: number): string {
  return `list:${page}:${perPage}`
}

function detailKey(slug: string): string {
  return `detail:${slug}`
}

async function readCache<T>(key: string): Promise<CachedEntry<T> | null> {
  const raw = (await storage().getItem<CachedEntry<T>>(key)) as CachedEntry<T> | null
  return raw ?? null
}

async function writeCache<T>(key: string, entry: CachedEntry<T>): Promise<void> {
  await storage().setItem(key, entry)
}

async function dropCache(key: string): Promise<void> {
  await storage().removeItem(key)
}

export interface EditorialCache {
  list(page: number, perPage: number): Promise<EditorialResult<ArticleListResult>>
  detail(slug: string): Promise<EditorialResult<ArticleDetail>>
}

export function createEditorialCache(options: EditorialApiOptions): EditorialCache {
  const api = createEditorialApi(options)

  return {
    async list(page, perPage) {
      const key = listKey(page, perPage)
      const cached = await readCache<ArticleListResult>(key)

      const firstResult = await api.list({
        page,
        perPage,
        ifNoneMatch: cached?.etag ?? null,
      })

      if (firstResult.status === "not_modified") {
        if (cached) {
          return {
            status: "ok",
            data: cached.data,
            etag: cached.etag,
            lastModified: cached.lastModified,
          }
        }
        // 304 sans corps local : Symfony a fait confiance à un ETag qu'on
        // n'a plus. On refait une requête sans validateur.
        const retry = await api.list({ page, perPage, ifNoneMatch: null })
        if (retry.status === "ok") {
          await writeCache(key, {
            data: retry.data,
            etag: retry.etag,
            lastModified: retry.lastModified,
            cachedAt: Date.now(),
          })
        }
        return retry
      }

      if (firstResult.status === "ok") {
        await writeCache(key, {
          data: firstResult.data,
          etag: firstResult.etag,
          lastModified: firstResult.lastModified,
          cachedAt: Date.now(),
        })
        return firstResult
      }

      // 4xx / 5xx / payload invalide : ne pas cacher, purger l'éventuelle
      // entrée existante afin que la prochaine tentative refasse un appel
      // complet plutôt que de relayer un cache éventuellement obsolète.
      if (cached) await dropCache(key)
      return firstResult
    },

    async detail(slug) {
      const key = detailKey(slug)
      const cached = await readCache<ArticleDetail>(key)

      const firstResult = await api.detail({
        slug,
        ifNoneMatch: cached?.etag ?? null,
      })

      if (firstResult.status === "not_modified") {
        if (cached) {
          return {
            status: "ok",
            data: cached.data,
            etag: cached.etag,
            lastModified: cached.lastModified,
          }
        }
        const retry = await api.detail({ slug, ifNoneMatch: null })
        if (retry.status === "ok") {
          await writeCache(key, {
            data: retry.data,
            etag: retry.etag,
            lastModified: retry.lastModified,
            cachedAt: Date.now(),
          })
        }
        return retry
      }

      if (firstResult.status === "ok") {
        await writeCache(key, {
          data: firstResult.data,
          etag: firstResult.etag,
          lastModified: firstResult.lastModified,
          cachedAt: Date.now(),
        })
        return firstResult
      }

      if (cached) await dropCache(key)
      return firstResult
    },
  }
}
