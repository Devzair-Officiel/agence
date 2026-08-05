/**
 * Service éditorial — accès réseau à l'API Symfony.
 *
 * Responsabilités :
 *   - construire les requêtes GET vers `/resources` et `/resources/{slug}`
 *     (le préfixe `/api` est intégré dans la base URL) ;
 *   - déléguer l'appel réseau à un adaptateur (`FetchLike`) injecté,
 *     afin de rester testable sans Nitro/Nuxt ;
 *   - normaliser toutes les issues HTTP (200/304/400/404/5xx/erreurs
 *     réseau) en un `EditorialResult` discriminé ;
 *   - valider chaque payload avec les mappers de `editorial-contract`.
 *
 * Le service NE gère PAS le cache serveur : la couche Nitro
 * (`server/utils/editorial-cache.ts`) l'entoure et lui passe le validateur
 * `If-None-Match` à utiliser vis-à-vis de Symfony (jamais celui du
 * navigateur — cf. ADR-011).
 */

import {
  EditorialContractError,
  mapArticleDetailResponse,
  mapArticleListResponse,
} from "~/utils/editorial-contract"
import type { ArticleDetail, ArticleListResult } from "~/types/editorial"

export interface FetchLikeResponse {
  status: number
  headers: Headers
  json(): Promise<unknown>
  text(): Promise<string>
}

export type FetchLike = (
  input: string,
  init?: {
    method?: string
    headers?: Record<string, string>
    signal?: AbortSignal
  },
) => Promise<FetchLikeResponse>

export type EditorialResult<TData> =
  | { status: "ok"; data: TData; etag: string | null; lastModified: string | null }
  | { status: "not_modified"; etag: string | null; lastModified: string | null }
  | { status: "not_found" }
  | { status: "bad_request"; messages: readonly string[] }
  | { status: "payload_invalid"; reason: string }
  | { status: "unavailable"; httpStatus: number | null }

export interface EditorialApiOptions {
  /** Base URL de l'API sans `/` final. Ex. `http://api:8000` (SSR) ou `/api` (client). */
  baseUrl: string
  /** Fetch injecté (Nitro `$fetch.raw` mappé, ou wrapper de test). */
  fetcher: FetchLike
  /** Timeout de sécurité (ms). Défaut : 4000. */
  timeoutMs?: number
}

export interface ListParams {
  page: number
  perPage: number
  /** Validateur envoyé à Symfony (jamais celui du navigateur). */
  ifNoneMatch?: string | null
}

export interface DetailParams {
  slug: string
  ifNoneMatch?: string | null
}

export function createEditorialApi(options: EditorialApiOptions) {
  const timeoutMs = options.timeoutMs ?? 4000
  const base = options.baseUrl.replace(/\/+$/, "")

  async function call(path: string, ifNoneMatch: string | null | undefined) {
    const headers: Record<string, string> = { Accept: "application/json" }
    if (ifNoneMatch) headers["If-None-Match"] = ifNoneMatch

    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), timeoutMs)
    try {
      const response = await options.fetcher(`${base}${path}`, {
        method: "GET",
        headers,
        signal: controller.signal,
      })
      return response
    } finally {
      clearTimeout(timer)
    }
  }

  return {
    async list(params: ListParams): Promise<EditorialResult<ArticleListResult>> {
      let response: FetchLikeResponse
      try {
        const query = new URLSearchParams({
          page: String(params.page),
          per_page: String(params.perPage),
        })
        response = await call(`/resources?${query.toString()}`, params.ifNoneMatch ?? null)
      } catch {
        return { status: "unavailable", httpStatus: null }
      }
      return handle(response, mapArticleListResponse)
    },

    async detail(params: DetailParams): Promise<EditorialResult<ArticleDetail>> {
      let response: FetchLikeResponse
      try {
        response = await call(
          `/resources/${encodeURIComponent(params.slug)}`,
          params.ifNoneMatch ?? null,
        )
      } catch {
        return { status: "unavailable", httpStatus: null }
      }
      return handle(response, mapArticleDetailResponse)
    },
  }
}

async function handle<TData>(
  response: FetchLikeResponse,
  mapper: (raw: unknown) => TData,
): Promise<EditorialResult<TData>> {
  const etag = response.headers.get("etag")
  const lastModified = response.headers.get("last-modified")

  if (response.status === 304) {
    return { status: "not_modified", etag, lastModified }
  }
  if (response.status === 404) {
    return { status: "not_found" }
  }
  if (response.status === 400) {
    let messages: string[] = []
    try {
      const body = (await response.json()) as { errors?: unknown }
      if (Array.isArray(body.errors)) {
        messages = body.errors.filter((m): m is string => typeof m === "string")
      }
    } catch {
      // JSON invalide : on ignore, on retourne un bad_request générique.
    }
    return { status: "bad_request", messages }
  }
  if (response.status < 200 || response.status >= 300) {
    return { status: "unavailable", httpStatus: response.status }
  }

  let payload: unknown
  try {
    payload = await response.json()
  } catch {
    return { status: "payload_invalid", reason: "JSON illisible depuis l'API éditoriale." }
  }

  try {
    return { status: "ok", data: mapper(payload), etag, lastModified }
  } catch (error) {
    const reason =
      error instanceof EditorialContractError
        ? error.message
        : "Payload invalide (raison inconnue)."
    return { status: "payload_invalid", reason }
  }
}
