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

  it("publishes all eight services (SEO-COM-5C); zero remain planned", () => {
    const published = servicePages.filter((p) => p.status === "published")
    const planned = servicePages.filter((p) => p.status === "planned")
    expect(published).toHaveLength(8)
    const publishedIds = published.map((p) => p.id)
    expect(publishedIds).toContain("creation-site-internet")
    expect(publishedIds).toContain("site-e-commerce")
    expect(publishedIds).toContain("application-web-metier")
    expect(publishedIds).toContain("design-ui-ux-identite-visuelle")
    expect(publishedIds).toContain("photographie-creation-contenu")
    expect(publishedIds).toContain("seo-referencement-naturel")
    expect(publishedIds).toContain("visibilite-locale")
    expect(publishedIds).toContain("maintenance-accompagnement")
    expect(planned).toHaveLength(0)
  })

  it("never carries an empty title, shortTitle or summary", () => {
    for (const page of servicePages) {
      expect(page.title.trim().length).toBeGreaterThan(0)
      expect(page.shortTitle.trim().length).toBeGreaterThan(0)
      expect(page.summary.trim().length).toBeGreaterThan(20)
    }
  })

  it("never carries an empty seoTitle or seoDescription", () => {
    for (const page of servicePages) {
      expect(page.seoTitle.trim().length).toBeGreaterThan(0)
      expect(page.seoDescription.trim().length).toBeGreaterThan(20)
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

  it("keeps seoDescriptions under 160 characters", () => {
    for (const page of servicePages) {
      expect(page.seoDescription.length).toBeLessThanOrEqual(160)
    }
  })
})

describe("resolveServiceRoute", () => {
  it("returns null for every planned service", () => {
    for (const page of servicePages.filter((p) => p.status === "planned")) {
      expect(resolveServiceRoute(page.id)).toBeNull()
    }
  })

  it("returns the route for creation-site-internet (published)", () => {
    expect(resolveServiceRoute("creation-site-internet")).toBe(
      "/services/creation-site-internet",
    )
  })

  it("returns null for unknown identifiers", () => {
    expect(resolveServiceRoute("marketing-affiliation")).toBeNull()
    expect(resolveServiceRoute("")).toBeNull()
    expect(resolveServiceRoute("creation-site-internet-xyz")).toBeNull()
  })
})
