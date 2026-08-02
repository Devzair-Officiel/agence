/**
 * Normalisation et validation de l'URL publique du site.
 *
 * Une seule source de vérité au runtime : `runtimeConfig.public.siteUrl`.
 * Chaque helper est pur, testable, sans dépendance à Nuxt ou Vue.
 *
 * Règles :
 *   - la valeur d'entrée peut arriver avec un slash final, un espace ou un
 *     protocole manquant : on normalise (trim + strip trailing slash) ;
 *   - une URL relative ou vide est INVALIDE : on remonte l'erreur au lieu
 *     de fabriquer silencieusement une valeur (canonicals cassées =
 *     pire qu'une exception au boot) ;
 *   - hors localhost/127.0.0.1, un protocole non https est un warning ;
 *   - on ne lit JAMAIS le header Host de la requête : le canonical vient
 *     toujours de cette configuration.
 */

const LOCAL_HOSTS = new Set(["localhost", "127.0.0.1", "0.0.0.0"])

export class SiteUrlError extends Error {
  constructor(message: string) {
    super(`[site-url] ${message}`)
    this.name = "SiteUrlError"
  }
}

/**
 * Retourne l'URL absolue du site, sans slash final.
 * Lance SiteUrlError si la valeur n'est pas exploitable pour un canonical.
 */
export function normalizeSiteUrl(rawSiteUrl: string | null | undefined): string {
  if (rawSiteUrl == null) {
    throw new SiteUrlError(
      "runtimeConfig.public.siteUrl est absent — définir NUXT_PUBLIC_SITE_URL.",
    )
  }

  const trimmed = String(rawSiteUrl).trim()
  if (trimmed.length === 0) {
    throw new SiteUrlError(
      "runtimeConfig.public.siteUrl est vide — définir NUXT_PUBLIC_SITE_URL.",
    )
  }

  let parsed: URL
  try {
    parsed = new URL(trimmed)
  } catch {
    throw new SiteUrlError(
      `siteUrl invalide : « ${trimmed} ». Attendu : URL absolue commençant par http(s)://`,
    )
  }

  if (parsed.protocol !== "http:" && parsed.protocol !== "https:") {
    throw new SiteUrlError(
      `siteUrl doit utiliser http:// ou https:// (reçu : ${parsed.protocol}).`,
    )
  }

  // Un canonical inclut origin + path ; ici, on ne conserve que l'origin.
  // Les chemins spécifiques à une page seront ajoutés par buildCanonical.
  return stripTrailingSlash(parsed.origin)
}

/**
 * Vrai si l'URL correspond à un environnement local (dev, docker, tests).
 * Utilisé pour tolérer http:// sans avertissement dans ces contextes.
 */
export function isLocalSiteUrl(siteUrl: string): boolean {
  try {
    const host = new URL(siteUrl).hostname
    return LOCAL_HOSTS.has(host)
  } catch {
    return false
  }
}

/**
 * Vrai si la production a été configurée avec une URL non sécurisée.
 * L'appelant peut décider de refuser le boot ou d'émettre un warning.
 */
export function isInsecureProductionUrl(siteUrl: string): boolean {
  if (isLocalSiteUrl(siteUrl)) return false
  return siteUrl.startsWith("http://")
}

function stripTrailingSlash(value: string): string {
  if (value.length <= 1) return value
  return value.endsWith("/") ? value.slice(0, -1) : value
}
