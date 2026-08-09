/**
 * Endpoint Nitro interne : liste paginée des ressources.
 *
 * Consommé par le composable `useResources` — jamais par le navigateur en
 * direct (aucun lien depuis les pages publiques). Le préfixe `_editorial`
 * évite explicitement le prefix `/api/*` qui est routé par Caddy vers
 * Symfony.
 *
 * Ne relaie PAS l'ETag JSON de Symfony au navigateur : ce serait mélanger
 * la validation d'une représentation JSON amont avec la représentation
 * HTML de la page Nuxt (cf. ADR-011). Seule la donnée validée est
 * retournée ; les statuts HTTP sont mappés vers l'état applicatif.
 */

import { EXPERTISE_IDS } from "~/config/expertise-pages"
import { editorialCache } from "~~/server/utils/editorial-runtime"

interface QueryShape {
  page?: unknown
  per_page?: unknown
  expertise?: unknown
}

function parseInteger(value: unknown, min: number, max: number, fallback: number): number | null {
  if (value === undefined || value === "") return fallback
  const raw = typeof value === "string" ? value : Array.isArray(value) ? value[0] : String(value)
  if (typeof raw !== "string" || !/^\d+$/.test(raw)) return null
  const parsed = Number.parseInt(raw, 10)
  if (parsed < min || parsed > max) return null
  return parsed
}

// Le filtre expertise est validé côté Nitro AVANT de solliciter Symfony :
// une valeur inconnue devient un 400 propre sans occuper l'API, et prévient
// aussi qu'un attaquant l'utilise pour sonder le back. L'allowlist côté
// Nuxt (`EXPERTISE_IDS`) est le miroir de l'enum PHP.
function parseExpertise(value: unknown): string | null | undefined {
  if (value === undefined || value === "" || value === null) return null
  const raw = typeof value === "string" ? value : Array.isArray(value) ? value[0] : null
  if (typeof raw !== "string" || raw.length === 0) return undefined
  return EXPERTISE_IDS.includes(raw) ? raw : undefined
}

export default defineEventHandler(async (event) => {
  const query = getQuery(event) as QueryShape
  const page = parseInteger(query.page, 1, 10_000, 1)
  const perPage = parseInteger(query.per_page, 1, 100, 6)
  const expertise = parseExpertise(query.expertise)

  if (page === null || perPage === null || expertise === undefined) {
    throw createError({ statusCode: 400, statusMessage: "Paramètres de listing invalides." })
  }

  const cache = editorialCache()
  const result = await cache.list(page, perPage, expertise)

  switch (result.status) {
    case "ok":
      // On ne pose PAS d'ETag ni de Cache-Control ici : cet endpoint est
      // interne (consommé par Nitro/SSR et par les navigations SPA du
      // composable), sa politique de cache n'est pas celle du navigateur.
      return result.data
    case "not_found":
      throw createError({ statusCode: 404, statusMessage: "Ressource introuvable." })
    case "bad_request":
      throw createError({ statusCode: 400, statusMessage: "Requête éditoriale invalide." })
    case "payload_invalid":
      throw createError({ statusCode: 502, statusMessage: "Payload éditorial invalide." })
    case "unavailable":
    case "not_modified":
      // 304 sans corps local (retry a échoué) est traité comme indisponibilité.
      throw createError({ statusCode: 503, statusMessage: "API éditoriale indisponible." })
  }
})
