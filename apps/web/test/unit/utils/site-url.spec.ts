import { describe, expect, it } from "vitest"
import {
  SiteUrlError,
  isInsecureProductionUrl,
  isLocalSiteUrl,
  normalizeSiteUrl,
} from "~/utils/site-url"

// Ces tests couvrent l'unique source de vérité de siteUrl.
// La qualité des canonicals et des URLs sociales dépend directement
// de la robustesse de normalizeSiteUrl.

describe("normalizeSiteUrl", () => {
  it("retourne l'origin sans slash final pour une URL absolue https", () => {
    expect(normalizeSiteUrl("https://devzair.fr")).toBe("https://devzair.fr")
    expect(normalizeSiteUrl("https://devzair.fr/")).toBe("https://devzair.fr")
  })

  it("supprime le chemin, la query et le fragment (seul l'origin est retenu)", () => {
    expect(normalizeSiteUrl("https://devzair.fr/agence?utm=1#foo")).toBe(
      "https://devzair.fr",
    )
  })

  it("préserve un port explicite", () => {
    expect(normalizeSiteUrl("http://localhost:3001")).toBe("http://localhost:3001")
    expect(normalizeSiteUrl("http://localhost:3001/")).toBe("http://localhost:3001")
  })

  it("supprime les espaces en tête et fin", () => {
    expect(normalizeSiteUrl("  https://devzair.fr  ")).toBe("https://devzair.fr")
  })

  it("refuse une valeur nulle ou vide", () => {
    expect(() => normalizeSiteUrl(null)).toThrow(SiteUrlError)
    expect(() => normalizeSiteUrl(undefined)).toThrow(SiteUrlError)
    expect(() => normalizeSiteUrl("")).toThrow(SiteUrlError)
    expect(() => normalizeSiteUrl("   ")).toThrow(SiteUrlError)
  })

  it("refuse une URL relative ou sans protocole", () => {
    expect(() => normalizeSiteUrl("/services")).toThrow(SiteUrlError)
    expect(() => normalizeSiteUrl("devzair.fr")).toThrow(SiteUrlError)
    expect(() => normalizeSiteUrl("//devzair.fr")).toThrow(SiteUrlError)
  })

  it("refuse un protocole non http(s)", () => {
    expect(() => normalizeSiteUrl("ftp://devzair.fr")).toThrow(SiteUrlError)
    expect(() => normalizeSiteUrl("file:///tmp/site")).toThrow(SiteUrlError)
  })
})

describe("isLocalSiteUrl", () => {
  it("reconnaît les hôtes locaux courants", () => {
    expect(isLocalSiteUrl("http://localhost:3001")).toBe(true)
    expect(isLocalSiteUrl("http://127.0.0.1")).toBe(true)
    expect(isLocalSiteUrl("http://0.0.0.0:3000")).toBe(true)
  })

  it("refuse les URLs publiques", () => {
    expect(isLocalSiteUrl("https://devzair.fr")).toBe(false)
    expect(isLocalSiteUrl("http://staging.devzair.fr")).toBe(false)
  })

  it("retourne false pour une valeur invalide plutôt que de crasher", () => {
    expect(isLocalSiteUrl("not-a-url")).toBe(false)
  })
})

describe("isInsecureProductionUrl", () => {
  it("détecte http:// sur un domaine public", () => {
    expect(isInsecureProductionUrl("http://devzair.fr")).toBe(true)
    expect(isInsecureProductionUrl("http://staging.devzair.fr")).toBe(true)
  })

  it("tolère http:// sur localhost", () => {
    expect(isInsecureProductionUrl("http://localhost:3001")).toBe(false)
  })

  it("accepte https:// sur un domaine public", () => {
    expect(isInsecureProductionUrl("https://devzair.fr")).toBe(false)
  })
})
