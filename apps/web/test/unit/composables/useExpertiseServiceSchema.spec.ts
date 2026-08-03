import { beforeEach, describe, expect, it, vi } from "vitest"
import { useExpertiseServiceSchema } from "~/composables/useExpertiseServiceSchema"

// Vérifie la construction du JSON-LD Service émis par les pages
// `/expertises/{slug}`. On stubbe useRuntimeConfig / useHead et on inspecte
// l'objet JSON-LD sérialisé.

interface CaptureBag {
  scripts: Array<Record<string, unknown>>
}

function installStubs(siteUrl: string): CaptureBag {
  const captures: CaptureBag = { scripts: [] }
  const globals = globalThis as unknown as {
    useRuntimeConfig: () => unknown
    useHead: (input: Record<string, unknown>) => void
  }
  globals.useRuntimeConfig = vi.fn(() => ({ public: { siteUrl } }))
  globals.useHead = vi.fn((input: Record<string, unknown>) => {
    const scripts = input.script as Array<Record<string, unknown>> | undefined
    if (scripts) captures.scripts.push(...scripts)
  })
  return captures
}

function readJsonLd(captures: CaptureBag): Record<string, unknown> {
  expect(captures.scripts).toHaveLength(1)
  const script = captures.scripts[0]!
  expect(script.type).toBe("application/ld+json")
  const raw = script.innerHTML as string
  return JSON.parse(raw) as Record<string, unknown>
}

describe("useExpertiseServiceSchema", () => {
  let captures: CaptureBag
  beforeEach(() => {
    captures = installStubs("https://devzair.fr")
  })

  it("émet un unique bloc application/ld+json avec le type Service", () => {
    useExpertiseServiceSchema({
      title: "Concevoir",
      description: "Description.",
      path: "/expertises/concevoir",
      serviceType: "Design",
    })
    const jsonLd = readJsonLd(captures)
    expect(jsonLd["@context"]).toBe("https://schema.org")
    expect(jsonLd["@type"]).toBe("Service")
  })

  it("construit un @id stable dérivé du canonical + #service", () => {
    useExpertiseServiceSchema({
      title: "Construire",
      description: "Description.",
      path: "/expertises/construire",
      serviceType: "Développement",
    })
    const jsonLd = readJsonLd(captures)
    expect(jsonLd["@id"]).toBe(
      "https://devzair.fr/expertises/construire#service",
    )
    expect(jsonLd.url).toBe("https://devzair.fr/expertises/construire")
  })

  it("référence l'Organization globale via son @id, pas par duplication", () => {
    useExpertiseServiceSchema({
      title: "SEO",
      description: "Description.",
      path: "/expertises/visibilite",
      serviceType: "SEO",
    })
    const jsonLd = readJsonLd(captures)
    expect(jsonLd.provider).toEqual({ "@id": "https://devzair.fr/#organization" })
  })

  it("n'émet AUCUN champ commercial (offers, price, aggregateRating, review)", () => {
    useExpertiseServiceSchema({
      title: "Faire évoluer",
      description: "Description.",
      path: "/expertises/faire-evoluer",
      serviceType: "Maintenance",
    })
    const jsonLd = readJsonLd(captures)
    expect(jsonLd).not.toHaveProperty("offers")
    expect(jsonLd).not.toHaveProperty("price")
    expect(jsonLd).not.toHaveProperty("priceRange")
    expect(jsonLd).not.toHaveProperty("priceSpecification")
    expect(jsonLd).not.toHaveProperty("aggregateRating")
    expect(jsonLd).not.toHaveProperty("review")
  })

  it("expose le serviceType tel qu'il est fourni par la page", () => {
    useExpertiseServiceSchema({
      title: "Valoriser",
      description: "Description.",
      path: "/expertises/valoriser",
      serviceType: "Contenus",
    })
    const jsonLd = readJsonLd(captures)
    expect(jsonLd.serviceType).toBe("Contenus")
    expect(jsonLd.name).toBe("Valoriser")
  })
})
