import { describe, expect, it, vi } from "vitest"
import { createEditorialApi, type FetchLike, type FetchLikeResponse } from "~/services/editorial-api"

/**
 * Service HTTP : couverture des statuts renvoyés par Symfony et
 * traduction en `EditorialResult` discriminé.
 */

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

const validListBody = {
  items: [
    {
      id: "01H",
      slug: "a-slug",
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

function api(fetcher: FetchLike) {
  return createEditorialApi({ baseUrl: "http://api:8000", fetcher })
}

describe("createEditorialApi.list", () => {
  it("retourne ok + etag pour un 200 valide", async () => {
    const fetcher: FetchLike = vi.fn(async () =>
      response({ status: 200, body: validListBody, headers: { etag: 'W/"abc"' } }),
    )
    const result = await api(fetcher).list({ page: 1, perPage: 6 })
    expect(result.status).toBe("ok")
    if (result.status === "ok") {
      expect(result.etag).toBe('W/"abc"')
      expect(result.data.items[0]!.slug).toBe("a-slug")
    }
  })

  it("relaie If-None-Match si fourni (jamais depuis le navigateur — c'est le cache local Nitro)", async () => {
    const fetcher = vi.fn(async () => response({ status: 304 }))
    await api(fetcher).list({ page: 1, perPage: 6, ifNoneMatch: 'W/"xyz"' })
    expect(fetcher).toHaveBeenCalledWith(
      expect.stringContaining("/resources?"),
      expect.objectContaining({
        headers: expect.objectContaining({ "If-None-Match": 'W/"xyz"' }),
      }),
    )
  })

  it("retourne not_modified sur 304", async () => {
    const fetcher: FetchLike = vi.fn(async () =>
      response({ status: 304, headers: { etag: 'W/"abc"' } }),
    )
    const result = await api(fetcher).list({ page: 1, perPage: 6 })
    expect(result.status).toBe("not_modified")
  })

  it("retourne not_found sur 404", async () => {
    const fetcher: FetchLike = vi.fn(async () => response({ status: 404 }))
    const result = await api(fetcher).list({ page: 999, perPage: 6 })
    expect(result.status).toBe("not_found")
  })

  it("retourne bad_request + messages sur 400", async () => {
    const fetcher: FetchLike = vi.fn(async () =>
      response({ status: 400, body: { errors: ["page invalide"] } }),
    )
    const result = await api(fetcher).list({ page: 0, perPage: 6 })
    expect(result.status).toBe("bad_request")
    if (result.status === "bad_request") {
      expect(result.messages).toEqual(["page invalide"])
    }
  })

  it("retourne unavailable sur 5xx", async () => {
    const fetcher: FetchLike = vi.fn(async () => response({ status: 503 }))
    const result = await api(fetcher).list({ page: 1, perPage: 6 })
    expect(result.status).toBe("unavailable")
  })

  it("retourne unavailable si la couche fetch jette (réseau, timeout…)", async () => {
    const fetcher: FetchLike = vi.fn(async () => {
      throw new Error("network")
    })
    const result = await api(fetcher).list({ page: 1, perPage: 6 })
    expect(result.status).toBe("unavailable")
  })

  it("retourne payload_invalid si le mapper échoue", async () => {
    const fetcher: FetchLike = vi.fn(async () =>
      response({ status: 200, body: { items: [{ slug: "BAD SLUG" }], pagination: {} } }),
    )
    const result = await api(fetcher).list({ page: 1, perPage: 6 })
    expect(result.status).toBe("payload_invalid")
  })
})

describe("createEditorialApi.detail", () => {
  const validDetailBody = {
    ...validListBody.items[0],
    body_markdown: "# T",
    content_html: "<p>T</p>",
    seo: { title: "T", description: "D" },
  }

  it("appelle /resources/{slug} en encodant le slug", async () => {
    const fetcher = vi.fn(async () => response({ status: 200, body: validDetailBody }))
    await api(fetcher).detail({ slug: "un-slug" })
    expect(fetcher).toHaveBeenCalledWith(
      "http://api:8000/resources/un-slug",
      expect.any(Object),
    )
  })

  it("retourne not_found sur 404", async () => {
    const fetcher: FetchLike = vi.fn(async () => response({ status: 404 }))
    const result = await api(fetcher).detail({ slug: "inconnu" })
    expect(result.status).toBe("not_found")
  })
})
