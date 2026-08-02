/**
 * Construction de l'URL canonique d'une page.
 *
 * Contrat :
 *   - le canonical est TOUJOURS absolu (origin + path) ;
 *   - il n'inclut jamais de query string ni de fragment (ces variantes ne
 *     doivent pas produire plusieurs URLs canoniques concurrentes) ;
 *   - il ne dépend jamais du header Host de la requête entrante ;
 *   - la racine `/` reste `/` (pas de canonical `https://devzair.fr` sans slash).
 *
 * Pour /design-preview (noindex, nofollow) : on ne génère PAS de canonical,
 * c'est la responsabilité de l'appelant (usePageSeo).
 */

import { normalizeSiteUrl } from "./site-url"

export interface BuildCanonicalOptions {
  /** URL absolue du site, telle que fournie par runtimeConfig.public.siteUrl. */
  readonly siteUrl: string
  /** Chemin de la page, absolu (`/services`) ou relatif — normalisé ici. */
  readonly path: string
}

/**
 * Retourne l'URL canonique absolue d'une page.
 * Lance SiteUrlError si siteUrl est invalide.
 */
export function buildCanonical(options: BuildCanonicalOptions): string {
  const origin = normalizeSiteUrl(options.siteUrl)
  const cleanPath = normalizeCanonicalPath(options.path)
  return `${origin}${cleanPath}`
}

/**
 * Nettoie le chemin fourni : garantit un slash de tête, supprime query et
 * fragment, préserve `/` pour la racine.
 *
 * Exporté pour test isolé et pour usage par buildOgUrl.
 */
export function normalizeCanonicalPath(path: string): string {
  const raw = String(path ?? "").trim()
  if (raw.length === 0) return "/"

  // On tronque à la première occurrence de ? ou # : les paramètres de
  // tracking et les ancres ne doivent jamais fuiter dans un canonical.
  const withoutQuery = raw.split(/[?#]/)[0] ?? "/"
  const withLeadingSlash = withoutQuery.startsWith("/")
    ? withoutQuery
    : `/${withoutQuery}`

  return withLeadingSlash
}

/**
 * Retourne une URL absolue à partir d'une ressource (image OG, favicon…).
 * Le chemin peut être :
 *   - déjà absolu (`https://...`) — retourné tel quel ;
 *   - relatif au site (`/og/default.png`) — préfixé par l'origin.
 */
export function buildAbsoluteAssetUrl(siteUrl: string, path: string): string {
  const raw = String(path ?? "").trim()
  if (/^https?:\/\//i.test(raw)) return raw

  const origin = normalizeSiteUrl(siteUrl)
  const withLeadingSlash = raw.startsWith("/") ? raw : `/${raw}`
  return `${origin}${withLeadingSlash}`
}
