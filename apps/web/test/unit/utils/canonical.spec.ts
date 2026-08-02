import { describe, expect, it } from "vitest"
import {
  buildAbsoluteAssetUrl,
  buildCanonical,
  normalizeCanonicalPath,
} from "~/utils/canonical"

describe("normalizeCanonicalPath", () => {
  it("ajoute le slash de tête si manquant", () => {
    expect(normalizeCanonicalPath("services")).toBe("/services")
  })

  it("préserve la racine", () => {
    expect(normalizeCanonicalPath("/")).toBe("/")
    expect(normalizeCanonicalPath("")).toBe("/")
  })

  it("retire query et fragment", () => {
    expect(normalizeCanonicalPath("/services?utm_source=x")).toBe("/services")
    expect(normalizeCanonicalPath("/services#anchor")).toBe("/services")
    expect(normalizeCanonicalPath("/services?a=1#anchor")).toBe("/services")
  })

  it("préserve les segments intermédiaires", () => {
    expect(normalizeCanonicalPath("/services/site-web")).toBe("/services/site-web")
  })

  it("supprime les espaces en tête et fin", () => {
    expect(normalizeCanonicalPath("  /services  ")).toBe("/services")
  })
})

describe("buildCanonical", () => {
  it("construit une URL absolue à partir de siteUrl et d'un chemin", () => {
    expect(buildCanonical({ siteUrl: "https://devzair.fr", path: "/services" })).toBe(
      "https://devzair.fr/services",
    )
  })

  it("gère la racine sans dupliquer les slashes", () => {
    expect(buildCanonical({ siteUrl: "https://devzair.fr", path: "/" })).toBe(
      "https://devzair.fr/",
    )
  })

  it("ne fuit jamais les paramètres de tracking", () => {
    expect(
      buildCanonical({
        siteUrl: "https://devzair.fr",
        path: "/services?utm_campaign=email",
      }),
    ).toBe("https://devzair.fr/services")
  })

  it("normalise siteUrl (retire le slash final)", () => {
    expect(buildCanonical({ siteUrl: "https://devzair.fr/", path: "/" })).toBe(
      "https://devzair.fr/",
    )
  })

  it("propage l'erreur si siteUrl est invalide", () => {
    expect(() => buildCanonical({ siteUrl: "", path: "/" })).toThrow()
    expect(() => buildCanonical({ siteUrl: "not-a-url", path: "/" })).toThrow()
  })
})

describe("buildAbsoluteAssetUrl", () => {
  it("préfixe une ressource relative avec l'origin", () => {
    expect(buildAbsoluteAssetUrl("https://devzair.fr", "/og/default.png")).toBe(
      "https://devzair.fr/og/default.png",
    )
    expect(buildAbsoluteAssetUrl("https://devzair.fr", "og/default.png")).toBe(
      "https://devzair.fr/og/default.png",
    )
  })

  it("laisse passer une URL déjà absolue", () => {
    expect(
      buildAbsoluteAssetUrl("https://devzair.fr", "https://cdn.example.com/x.png"),
    ).toBe("https://cdn.example.com/x.png")
    expect(
      buildAbsoluteAssetUrl("https://devzair.fr", "http://cdn.example.com/x.png"),
    ).toBe("http://cdn.example.com/x.png")
  })
})
