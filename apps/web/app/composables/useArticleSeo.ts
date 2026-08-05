/**
 * SEO structurel pour la page détail d'un article.
 *
 * Deux responsabilités :
 *   1. déléguer les balises génériques (title, description, canonical, OG,
 *      Twitter, robots) à `usePageSeo` — pas de duplication ;
 *   2. injecter en plus deux blocs JSON-LD `BlogPosting` et `BreadcrumbList`
 *      via `useHead`.
 *
 * Le JSON-LD référence l'`Organization` du site via `@id` pour former un
 * graphe cohérent avec `useSiteSchema` (posé dans le layout par défaut).
 * Aucune donnée fictive n'est ajoutée : seuls les champs réellement
 * connus depuis l'article sont émis (règle 1 du référentiel).
 */

import type { ArticleDetail } from "~/types/editorial"
import { site } from "~/config/site"
import { buildCanonical } from "~/utils/canonical"
import { normalizeSiteUrl } from "~/utils/site-url"

export interface UseArticleSeoArgs {
  article: ArticleDetail
  /** Chemin canonique de la page. Ex. `/ressources/mon-article`. */
  path: string
}

export function useArticleSeo({ article, path }: UseArticleSeoArgs): void {
  const config = useRuntimeConfig()
  const siteUrl = config.public.siteUrl as string
  const origin = normalizeSiteUrl(siteUrl)
  const canonical = buildCanonical({ siteUrl, path })

  usePageSeo({
    title: article.seo.title,
    description: article.seo.description,
    path,
    type: "article",
  })

  const blogPosting: Record<string, unknown> = {
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    headline: article.title,
    description: article.seo.description,
    mainEntityOfPage: canonical,
    inLanguage: site.language,
    datePublished: article.publishedAt,
    dateModified: article.updatedAt,
    author: {
      "@type": article.author.type === "person" ? "Person" : "Organization",
      name: article.author.name,
    },
    publisher: { "@id": `${origin}/#organization` },
  }

  const breadcrumb = {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: [
      {
        "@type": "ListItem",
        position: 1,
        name: "Accueil",
        item: origin,
      },
      {
        "@type": "ListItem",
        position: 2,
        name: "Ressources",
        item: buildCanonical({ siteUrl, path: "/ressources" }),
      },
      {
        "@type": "ListItem",
        position: 3,
        name: article.title,
        item: canonical,
      },
    ],
  }

  useHead({
    script: [
      {
        id: "devzair-article-schema",
        type: "application/ld+json",
        innerHTML: JSON.stringify(blogPosting),
      },
      {
        id: "devzair-article-breadcrumb",
        type: "application/ld+json",
        innerHTML: JSON.stringify(breadcrumb),
      },
    ],
  })
}
