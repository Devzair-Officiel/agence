/**
 * Fabrique unique du cache éditorial côté Nitro.
 *
 * Consomme la runtime config privée `editorialApiBaseUrl` — impérativement
 * définie (via `NUXT_EDITORIAL_API_BASE_URL`), sinon Nitro échoue vite
 * plutôt que de tenter des appels vides. Fournit le fetcher basé sur le
 * `$fetch` Nitro (retour `.raw`) pour rester dans l'écosystème unfetch et
 * bénéficier des timeouts natifs.
 */

import type { FetchLike } from "~/services/editorial-api"
import { createEditorialCache, type EditorialCache } from "./editorial-cache"

let cached: EditorialCache | null = null

function nitroFetcher(): FetchLike {
  return async (input, init) => {
    const response = await $fetch.raw(input, {
      method: init?.method as "GET" | undefined,
      headers: init?.headers,
      signal: init?.signal,
      // Nitro suit par défaut les 2xx uniquement. On accepte tous statuts
      // pour pouvoir traiter 304/400/404/5xx dans le service.
      ignoreResponseError: true,
      // Ne pas parser en JSON automatiquement pour préserver text() si besoin.
      responseType: "json",
    })
    const headers = new Headers()
    // `response.headers` est un `Headers` ou similaire selon la version
    // d'unfetch/ofetch — on itère de manière défensive.
    for (const [key, value] of response.headers.entries()) {
      headers.set(key, value)
    }
    const body = response._data
    return {
      status: response.status,
      headers,
      async json() {
        return body
      },
      async text() {
        return typeof body === "string" ? body : JSON.stringify(body ?? "")
      },
    }
  }
}

export function editorialCache(): EditorialCache {
  if (cached) return cached
  const config = useRuntimeConfig()
  const baseUrl = (config.editorialApiBaseUrl as string | undefined) ?? ""
  if (!baseUrl) {
    throw createError({
      statusCode: 503,
      statusMessage:
        "NUXT_EDITORIAL_API_BASE_URL non configuré — pipeline éditorial indisponible.",
    })
  }
  cached = createEditorialCache({
    baseUrl,
    fetcher: nitroFetcher(),
  })
  return cached
}

/**
 * Réinitialise la mémoïsation — utile en test uniquement, jamais en runtime.
 */
export function __resetEditorialCacheForTests(): void {
  cached = null
}
