/**
 * usePageSeo — composable SEO centralisé de Devzair.
 *
 * Une page ne doit JAMAIS appeler `useSeoMeta`, `useHead` ou construire un
 * canonical à la main. Elle passe par usePageSeo(input) et reçoit :
 *   - title (avec titleTemplate global appliqué par nuxt.config)
 *   - meta description
 *   - canonical absolu construit à partir de siteUrl + input.path
 *   - Open Graph (title, description, url, type, site_name, locale, image)
 *   - Twitter/X Card (summary_large_image quand image, summary sinon)
 *   - meta robots — soit forcée par la page, soit dérivée de la politique globale
 *
 * Interaction avec la politique d'indexation :
 *   - `noindex: true` (défaut sur pages internes) force `noindex, nofollow`
 *     ET supprime le canonical (rien à canonicalisé si non indexé)
 *   - `noindex: false` laisse @nuxtjs/robots trancher : en mode
 *     siteIndexable=false, `X-Robots-Tag: noindex` est imposé globalement,
 *     et la page reste sûre même sans meta robots explicite ici
 *
 * `path` est FOURNI par la page. On ne lit pas `useRoute().path` : les tests
 * unitaires deviennent triviaux et le chemin canonique est explicite.
 */

import { buildAbsoluteAssetUrl, buildCanonical } from "~/utils/canonical"
import { site } from "~/config/site"

export type PageSeoImageType = string
export type PageSeoRobotsType =
  | "index, follow"
  | "noindex, follow"
  | "index, nofollow"
  | "noindex, nofollow"

export interface PageSeoInput {
  /** Titre spécifique de la page (le template global ajoute « | Devzair »). */
  title: string
  /** Description propre à la page ; utilisée aussi en OG et Twitter. */
  description: string
  /** Chemin canonique : `/`, `/services`, `/ressources/mon-article`… */
  path: string
  /** Image OG absolue ou relative au site (défaut : site.defaultOgImage). */
  image?: PageSeoImageType
  /** Texte alternatif de l'image OG. */
  imageAlt?: string
  /** Type Open Graph. */
  type?: "website" | "article"
  /**
   * Marque la page comme non indexable.
   * Force `noindex, nofollow` et supprime le canonical.
   */
  noindex?: boolean
  /**
   * Directive robots complète, si la page a un besoin très spécifique.
   * Prioritaire sur `noindex`. Éviter, sauf cas justifié.
   */
  robots?: PageSeoRobotsType
}

export function usePageSeo(input: PageSeoInput): void {
  const config = useRuntimeConfig()
  const siteUrl = config.public.siteUrl as string

  const isNoindex = input.noindex === true
  const robotsDirective = input.robots ?? (isNoindex ? "noindex, nofollow" : undefined)

  // Un canonical sur une page noindex n'apporte rien : l'URL n'est pas
  // destinée aux résultats de recherche. On préfère l'omettre pour rester
  // cohérent avec robots.
  const canonicalUrl = isNoindex ? null : buildCanonical({ siteUrl, path: input.path })
  const ogUrl = canonicalUrl ?? buildCanonical({ siteUrl, path: input.path })

  const imageSource = input.image ?? site.defaultOgImage ?? null
  const imageUrl = imageSource ? buildAbsoluteAssetUrl(siteUrl, imageSource) : null
  const twitterCard = imageUrl ? "summary_large_image" : "summary"

  useSeoMeta({
    title: input.title,
    description: input.description,
    ogTitle: input.title,
    ogDescription: input.description,
    ogType: input.type ?? "website",
    ogUrl,
    ogSiteName: site.name,
    ogLocale: site.locale,
    ogImage: imageUrl ?? undefined,
    ogImageAlt: imageUrl ? input.imageAlt : undefined,
    twitterCard,
    twitterTitle: input.title,
    twitterDescription: input.description,
    twitterImage: imageUrl ?? undefined,
    ...(robotsDirective ? { robots: robotsDirective } : {}),
  })

  // Le canonical passe par useHead : useSeoMeta ne gère pas les <link>.
  if (canonicalUrl) {
    useHead({
      link: [{ rel: "canonical", href: canonicalUrl }],
    })
  }
}
