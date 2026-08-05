/**
 * Endpoint Nitro interne : détail d'une ressource par slug.
 *
 * Idem `_editorial/list` — utilise le cache serveur, ne relaie pas l'ETag
 * JSON amont vers le navigateur (cf. ADR-011).
 */

import { editorialCache } from "~~/server/utils/editorial-runtime"

const SLUG_PATTERN = /^[a-z0-9]+(?:-[a-z0-9]+)*$/u

export default defineEventHandler(async (event) => {
  const slug = getRouterParam(event, "slug")
  if (typeof slug !== "string" || !SLUG_PATTERN.test(slug) || slug.length > 200) {
    throw createError({ statusCode: 400, statusMessage: "Slug invalide." })
  }

  const cache = editorialCache()
  const result = await cache.detail(slug)

  switch (result.status) {
    case "ok":
      return result.data
    case "not_found":
      throw createError({ statusCode: 404, statusMessage: "Ressource introuvable." })
    case "bad_request":
      throw createError({ statusCode: 400, statusMessage: "Requête éditoriale invalide." })
    case "payload_invalid":
      throw createError({ statusCode: 502, statusMessage: "Payload éditorial invalide." })
    case "unavailable":
    case "not_modified":
      throw createError({ statusCode: 503, statusMessage: "API éditoriale indisponible." })
  }
})
