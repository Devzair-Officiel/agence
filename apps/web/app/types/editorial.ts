/**
 * Contrats métier éditoriaux côté frontend.
 *
 * Ces types représentent les objets métier une fois normalisés (camelCase,
 * dates typées, tableaux garantis). Ils sont produits par
 * `app/utils/editorial-contract.ts` à partir du contrat JSON `snake_case`
 * exposé par l'API Symfony (`/api/resources`, `/api/resources/{slug}`).
 *
 * Le contrat JSON brut n'est jamais exposé au reste du front : composables,
 * pages et composants ne manipulent que ces types. Toute évolution du
 * contrat API doit passer par le mapper et ses tests.
 */

export type AuthorType = "organization" | "person"

export interface ArticleAuthor {
  name: string
  type: AuthorType
}

export interface ArticleSeo {
  title: string
  description: string
}

/** Résumé d'article servi dans les listes paginées. */
export interface ArticleSummary {
  id: string
  slug: string
  title: string
  excerpt: string
  author: ArticleAuthor
  expertiseIds: readonly string[]
  publishedAt: string
  updatedAt: string
}

/**
 * Article complet servi sur le détail.
 *
 * `contentHtml` est produit et sécurisé côté Symfony (ADR-011). C'est la
 * seule représentation à afficher publiquement. `bodyMarkdown` est conservé
 * pour d'éventuels usages hors rendu public (debug, exports) — le rendu
 * public NE DOIT PAS le convertir côté front.
 */
export interface ArticleDetail extends ArticleSummary {
  bodyMarkdown: string
  contentHtml: string
  seo: ArticleSeo
}

export interface Pagination {
  page: number
  perPage: number
  total: number
  totalPages: number
}

export interface ArticleListResult {
  items: readonly ArticleSummary[]
  pagination: Pagination
}
