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

/**
 * Variant WebP optimisé (card ≤768×432, hero ≤1600×900, ratio 16:9 exact).
 * Absent si l'asset est antérieur à la Phase 9C (pipeline GD non encore exécuté).
 */
export interface ArticleHeroImageVariant {
  url: string
  width: number
  height: number
}

/**
 * Image principale d'un article (Phase 9B/9C).
 *
 * `url` : original normalisé — fallback legacy.
 * `card` / `hero` : variants WebP 16:9 générés par le pipeline d'upload (Phase 9C).
 * Utiliser `card?.url ?? url` dans les listes, `hero?.url ?? url` sur le détail.
 * Les dimensions exposées par le variant sont réelles (pas de valeurs par défaut hardcodées).
 */
export interface ArticleHeroImage {
  url: string
  alt: string
  width: number
  height: number
  mimeType: string
  card: ArticleHeroImageVariant | null
  hero: ArticleHeroImageVariant | null
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
  /**
   * `null` explicite quand le back-office n'a pas encore associé d'image
   * — le contrat API expose systématiquement la clé, jamais absente.
   */
  heroImage: ArticleHeroImage | null
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
