import { describe, expect, it } from "vitest"
import { resolveServiceRoute, servicePages } from "~/config/service-pages"

describe("servicePages", () => {
  it("declares exactly eight definitions", () => {
    expect(servicePages).toHaveLength(8)
  })

  it("uses unique ids and slugs", () => {
    const ids = servicePages.map((p) => p.id)
    const slugs = servicePages.map((p) => p.slug)
    expect(new Set(ids).size).toBe(ids.length)
    expect(new Set(slugs).size).toBe(slugs.length)
  })

  it("keeps id equal to slug (stable one-to-one mapping)", () => {
    for (const page of servicePages) {
      expect(page.id).toBe(page.slug)
    }
  })

  it("builds routes as `/services/{slug}` with a leading slash", () => {
    for (const page of servicePages) {
      expect(page.route).toBe(`/services/${page.slug}`)
      expect(page.route.startsWith("/")).toBe(true)
    }
  })

  it("uses only lowercase URL-safe slugs (letters, digits, hyphens)", () => {
    const slugPattern = /^[a-z0-9]+(-[a-z0-9]+)*$/
    for (const page of servicePages) {
      expect(page.slug).toMatch(slugPattern)
    }
  })

  it("starts all definitions at `planned` status", () => {
    for (const page of servicePages) {
      expect(page.status).toBe("planned")
    }
  })

  it("never carries an empty title, shortTitle or summary", () => {
    for (const page of servicePages) {
      expect(page.title.trim().length).toBeGreaterThan(0)
      expect(page.shortTitle.trim().length).toBeGreaterThan(0)
      expect(page.summary.trim().length).toBeGreaterThan(20)
    }
  })

  it("includes the eight expected service slugs", () => {
    const slugs = servicePages.map((p) => p.slug)
    expect(slugs).toContain("creation-site-internet")
    expect(slugs).toContain("site-e-commerce")
    expect(slugs).toContain("application-web-metier")
    expect(slugs).toContain("design-ui-ux-identite-visuelle")
    expect(slugs).toContain("photographie-creation-contenu")
    expect(slugs).toContain("seo-referencement-naturel")
    expect(slugs).toContain("visibilite-locale")
    expect(slugs).toContain("maintenance-accompagnement")
  })

  it("keeps summaries under 160 characters", () => {
    for (const page of servicePages) {
      expect(page.summary.length).toBeLessThanOrEqual(160)
    }
  })
})

describe("resolveServiceRoute", () => {
  it("returns null for every planned service", () => {
    for (const page of servicePages) {
      expect(resolveServiceRoute(page.id)).toBeNull()
    }
  })

  it("returns null for unknown identifiers", () => {
    expect(resolveServiceRoute("marketing-affiliation")).toBeNull()
    expect(resolveServiceRoute("")).toBeNull()
    expect(resolveServiceRoute("creation-site-internet-xyz")).toBeNull()
  })
})
