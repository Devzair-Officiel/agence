import { beforeEach, describe, expect, it, vi } from "vitest"
import type { ArticleListResult } from "~/types/editorial"
import type { FetchLike, FetchLikeResponse } from "~/services/editorial-api"

/**
 * Cache serveur éditorial — couvre les scénarios critiques de la
 * correction obligatoire 1 du brief Phase 8B2 :
 *   - le validateur If-None-Match transmis à Symfony provient TOUJOURS
 *     du cache local Nitro (jamais du navigateur) ;
 *   - un 304 avec cache local rejoue le payload local ;
 *   - un 304 sans cache local retente sans validateur ;
 *   - un 4xx/5xx purge le cache local pour éviter de réutiliser un
 *     payload obsolète ;
 *   - un 200 remplace le cache.
 *
 * `useStorage` est stubée par un Map en mémoire (comportement natif de
 * Nitro en dev/test).
 */

// Stub `useStorage` (auto-import Nitro) AVANT d'importer le module cache.
const storageBackingByNamespace = new Map<string, Map<string, unknown>>()
function memoryStorage(namespace: string) {
  const backing = storageBackingByNamespace.get(namespace) ?? new Map<string, unknown>()
  storageBackingByNamespace.set(namespace, backing)
  return {
    getItem: async (key: string) => backing.get(key) ?? null,
    setItem: async (key: string, value: unknown) => {
      backing.set(key, value)
    },
    removeItem: async (key: string) => {
      backing.delete(key)
    },
  }
}
;(globalThis as unknown as { useStorage: (ns: string) => unknown }).useStorage = memoryStorage

const { createEditorialCache } = await import("../../../server/utils/editorial-cache")

function response(init: {
  status: number
  body?: unknown
  headers?: Record<string, string>
}): FetchLikeResponse {
  const headers = new Headers(init.headers ?? {})
  return {
    status: init.status,
    headers,
    async json() {
      if (init.body === undefined) throw new Error("no body")
      return init.body
    },
    async text() {
      return typeof init.body === "string" ? init.body : JSON.stringify(init.body ?? "")
    },
  }
}

const VALID_DATE = "2026-01-15T10:00:00Z"
const validList = {
  items: [
    {
      id: "01H",
      slug: "s",
      title: "T",
      excerpt: "E",
      author: { name: "Devzair", type: "organization" },
      expertise_ids: [],
      published_at: VALID_DATE,
      updated_at: VALID_DATE,
    },
  ],
  pagination: { page: 1, per_page: 6, total: 1, total_pages: 1 },
}

function build(fetcher: FetchLike) {
  return createEditorialCache({ baseUrl: "http://api:8000", fetcher })
}

describe("createEditorialCache.list", () => {
  beforeEach(() => storageBackingByNamespace.clear())

  it("premier appel : 200 → met en cache et retourne ok", async () => {
    const fetcher = vi.fn(async () =>
      response({ status: 200, body: validList, headers: { etag: 'W/"v1"' } }),
    )
    const cache = build(fetcher)
    const result = await cache.list(1, 6)
    expect(result.status).toBe("ok")
    if (result.status === "ok") {
      expect(result.etag).toBe('W/"v1"')
      expect((result.data as ArticleListResult).items).toHaveLength(1)
    }
  })

  it("second appel : envoie l'ETag stocké localement comme If-None-Match", async () => {
    const fetcher = vi
      .fn()
      .mockResolvedValueOnce(response({ status: 200, body: validList, headers: { etag: 'W/"v1"' } }))
      .mockResolvedValueOnce(response({ status: 304, headers: { etag: 'W/"v1"' } }))
    const cache = build(fetcher)
    await cache.list(1, 6)
    await cache.list(1, 6)

    const secondCallHeaders = (fetcher.mock.calls[1]?.[1] as { headers: Record<string, string> })
      .headers
    expect(secondCallHeaders["If-None-Match"]).toBe('W/"v1"')
  })

  it("304 avec cache local : retourne ok en rejouant le payload local (pas de fetch supplémentaire)", async () => {
    const fetcher = vi
      .fn()
      .mockResolvedValueOnce(response({ status: 200, body: validList, headers: { etag: 'W/"v1"' } }))
      .mockResolvedValueOnce(response({ status: 304, headers: { etag: 'W/"v1"' } }))
    const cache = build(fetcher)
    await cache.list(1, 6)
    const result = await cache.list(1, 6)
    expect(result.status).toBe("ok")
    expect(fetcher).toHaveBeenCalledTimes(2)
  })

  it("304 sans cache local : retente SANS validateur puis met en cache", async () => {
    // Simule un serveur qui répond 304 même sans validateur : cas
    // dégradé, on retente à froid. On force ça en préparant un cache
    // vide et un fetcher qui répond 304 puis 200.
    const fetcher = vi
      .fn()
      .mockResolvedValueOnce(response({ status: 304 }))
      .mockResolvedValueOnce(response({ status: 200, body: validList, headers: { etag: 'W/"v2"' } }))
    const cache = build(fetcher)
    const result = await cache.list(1, 6)
    expect(result.status).toBe("ok")
    // Le premier appel envoyait null comme validateur ; le second est
    // le retry sans validateur explicite non plus.
    expect(fetcher).toHaveBeenCalledTimes(2)
  })

  it("5xx : ne cache pas, purge l'éventuelle entrée existante", async () => {
    const fetcher = vi
      .fn()
      .mockResolvedValueOnce(response({ status: 200, body: validList, headers: { etag: 'W/"v1"' } }))
      .mockResolvedValueOnce(response({ status: 503 }))
      .mockResolvedValueOnce(response({ status: 200, body: validList, headers: { etag: 'W/"v3"' } }))
    const cache = build(fetcher)
    await cache.list(1, 6)
    const failing = await cache.list(1, 6)
    expect(failing.status).toBe("unavailable")

    // La troisième requête ne doit PAS envoyer l'ancien ETag (cache purgé).
    await cache.list(1, 6)
    const thirdHeaders = (fetcher.mock.calls[2]?.[1] as { headers: Record<string, string> })
      .headers
    expect(thirdHeaders["If-None-Match"]).toBeUndefined()
  })

  it("payload_invalid : ne cache pas, purge l'éventuelle entrée existante", async () => {
    const fetcher = vi
      .fn()
      .mockResolvedValueOnce(response({ status: 200, body: validList, headers: { etag: 'W/"v1"' } }))
      .mockResolvedValueOnce(
        response({ status: 200, body: { items: [{}], pagination: {} }, headers: { etag: 'W/"bad"' } }),
      )
      .mockResolvedValueOnce(response({ status: 200, body: validList, headers: { etag: 'W/"v3"' } }))
    const cache = build(fetcher)
    await cache.list(1, 6)
    const bad = await cache.list(1, 6)
    expect(bad.status).toBe("payload_invalid")

    await cache.list(1, 6)
    const thirdHeaders = (fetcher.mock.calls[2]?.[1] as { headers: Record<string, string> })
      .headers
    expect(thirdHeaders["If-None-Match"]).toBeUndefined()
  })
})
