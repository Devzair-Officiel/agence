/**
 * Frontière entre le contrat JSON `snake_case` de l'API Symfony et les
 * objets métier `camelCase` typés utilisés par le front (types dans
 * `app/types/editorial.ts`).
 *
 * Cette couche est volontairement défensive :
 *   - chaque champ attendu est validé (type, format, non-vacuité) ;
 *   - toute donnée manquante ou malformée fait échouer le mapping avec
 *     `EditorialContractError` — la couche service transformera cela en
 *     erreur applicative « payload_invalid ».
 *
 * Aucune dépendance à Nuxt/Vue ici : pur TypeScript, testable en isolation
 * dans `test/unit/utils/editorial-contract.spec.ts`.
 */

import type {
  ArticleAuthor,
  ArticleDetail,
  ArticleListResult,
  ArticleSummary,
  AuthorType,
  Pagination,
} from "~/types/editorial"

export class EditorialContractError extends Error {
  constructor(message: string) {
    super(`Contrat éditorial invalide : ${message}`)
    this.name = "EditorialContractError"
  }
}

const AUTHOR_TYPES = new Set<AuthorType>(["organization", "person"])
const SLUG_PATTERN = /^[a-z0-9]+(?:-[a-z0-9]+)*$/u
const ISO_DATE_PATTERN = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/u

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null && !Array.isArray(value)
}

function requireString(source: Record<string, unknown>, key: string, label: string): string {
  const raw = source[key]
  if (typeof raw !== "string" || raw.length === 0) {
    throw new EditorialContractError(`${label} manquant ou non-string (clé ${key}).`)
  }
  return raw
}

function requireOptionalString(
  source: Record<string, unknown>,
  key: string,
  label: string,
): string {
  const raw = source[key]
  if (raw === undefined || raw === null) return ""
  if (typeof raw !== "string") {
    throw new EditorialContractError(`${label} doit être une chaîne (clé ${key}).`)
  }
  return raw
}

function requireIsoDate(source: Record<string, unknown>, key: string, label: string): string {
  const raw = requireString(source, key, label)
  if (!ISO_DATE_PATTERN.test(raw)) {
    throw new EditorialContractError(`${label} n'est pas une date ISO 8601 (${raw}).`)
  }
  return raw
}

function requireSlug(source: Record<string, unknown>, key: string, label: string): string {
  const raw = requireString(source, key, label)
  if (!SLUG_PATTERN.test(raw)) {
    throw new EditorialContractError(`${label} n'est pas un slug URL-safe (${raw}).`)
  }
  return raw
}

function requireInteger(
  source: Record<string, unknown>,
  key: string,
  label: string,
  minimum: number,
): number {
  const raw = source[key]
  if (typeof raw !== "number" || !Number.isInteger(raw) || raw < minimum) {
    throw new EditorialContractError(
      `${label} doit être un entier ≥ ${minimum} (clé ${key}, reçu ${String(raw)}).`,
    )
  }
  return raw
}

function requireStringArray(
  source: Record<string, unknown>,
  key: string,
  label: string,
): string[] {
  const raw = source[key]
  if (!Array.isArray(raw)) {
    throw new EditorialContractError(`${label} doit être un tableau (clé ${key}).`)
  }
  return raw.map((entry, index) => {
    if (typeof entry !== "string" || entry.length === 0) {
      throw new EditorialContractError(`${label}[${index}] doit être une chaîne non vide.`)
    }
    return entry
  })
}

function mapAuthor(raw: unknown): ArticleAuthor {
  if (!isRecord(raw)) {
    throw new EditorialContractError("author doit être un objet.")
  }
  const name = requireString(raw, "name", "author.name")
  const type = requireString(raw, "type", "author.type")
  if (!AUTHOR_TYPES.has(type as AuthorType)) {
    throw new EditorialContractError(`author.type inconnu (${type}).`)
  }
  return { name, type: type as AuthorType }
}

function mapSummary(raw: unknown): ArticleSummary {
  if (!isRecord(raw)) {
    throw new EditorialContractError("item de liste doit être un objet.")
  }
  return {
    id: requireString(raw, "id", "id"),
    slug: requireSlug(raw, "slug", "slug"),
    title: requireString(raw, "title", "title"),
    excerpt: requireString(raw, "excerpt", "excerpt"),
    author: mapAuthor(raw.author),
    expertiseIds: requireStringArray(raw, "expertise_ids", "expertise_ids"),
    publishedAt: requireIsoDate(raw, "published_at", "published_at"),
    updatedAt: requireIsoDate(raw, "updated_at", "updated_at"),
  }
}

function mapPagination(raw: unknown): Pagination {
  if (!isRecord(raw)) {
    throw new EditorialContractError("pagination doit être un objet.")
  }
  const page = requireInteger(raw, "page", "pagination.page", 1)
  const perPage = requireInteger(raw, "per_page", "pagination.per_page", 1)
  const total = requireInteger(raw, "total", "pagination.total", 0)
  const totalPages = requireInteger(raw, "total_pages", "pagination.total_pages", 0)
  return { page, perPage, total, totalPages }
}

export function mapArticleListResponse(raw: unknown): ArticleListResult {
  if (!isRecord(raw)) {
    throw new EditorialContractError("réponse liste doit être un objet.")
  }
  const items = raw.items
  if (!Array.isArray(items)) {
    throw new EditorialContractError("items doit être un tableau.")
  }
  return {
    items: items.map(mapSummary),
    pagination: mapPagination(raw.pagination),
  }
}

export function mapArticleDetailResponse(raw: unknown): ArticleDetail {
  if (!isRecord(raw)) {
    throw new EditorialContractError("réponse détail doit être un objet.")
  }
  const seo = raw.seo
  if (!isRecord(seo)) {
    throw new EditorialContractError("seo doit être un objet.")
  }
  return {
    id: requireString(raw, "id", "id"),
    slug: requireSlug(raw, "slug", "slug"),
    title: requireString(raw, "title", "title"),
    excerpt: requireString(raw, "excerpt", "excerpt"),
    bodyMarkdown: requireOptionalString(raw, "body_markdown", "body_markdown"),
    contentHtml: requireString(raw, "content_html", "content_html"),
    seo: {
      title: requireString(seo, "title", "seo.title"),
      description: requireString(seo, "description", "seo.description"),
    },
    author: mapAuthor(raw.author),
    expertiseIds: requireStringArray(raw, "expertise_ids", "expertise_ids"),
    publishedAt: requireIsoDate(raw, "published_at", "published_at"),
    updatedAt: requireIsoDate(raw, "updated_at", "updated_at"),
  }
}
