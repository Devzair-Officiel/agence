import { describe, expect, it } from "vitest"
import { caseStudies } from "~/config/case-studies"

describe("caseStudies", () => {
  it("declares exactly six case studies", () => {
    expect(caseStudies).toHaveLength(6)
  })

  it("uses unique ids and slugs", () => {
    const ids = caseStudies.map((cs) => cs.id)
    const slugs = caseStudies.map((cs) => cs.slug)
    expect(new Set(ids).size).toBe(ids.length)
    expect(new Set(slugs).size).toBe(slugs.length)
  })

  it("keeps id equal to slug (stable one-to-one mapping)", () => {
    for (const cs of caseStudies) {
      expect(cs.id).toBe(cs.slug)
    }
  })

  it("builds routes as `/realisations/{slug}` with a leading slash", () => {
    for (const cs of caseStudies) {
      expect(cs.route).toBe(`/realisations/${cs.slug}`)
      expect(cs.route.startsWith("/")).toBe(true)
    }
  })

  it("uses only lowercase URL-safe slugs (letters, digits, hyphens)", () => {
    const slugPattern = /^[a-z0-9]+(-[a-z0-9]+)*$/
    for (const cs of caseStudies) {
      expect(cs.slug).toMatch(slugPattern)
    }
  })

  it("publishes exactly four studies; e-shop-admin and haramain-prestige remain planned", () => {
    const published = caseStudies.filter((cs) => cs.status === "published")
    const planned = caseStudies.filter((cs) => cs.status === "planned")
    expect(published).toHaveLength(4)
    expect(planned).toHaveLength(2)
    const plannedIds = planned.map((cs) => cs.id)
    expect(plannedIds).toContain("e-shop-admin")
    expect(plannedIds).toContain("haramain-prestige")
  })

  it("includes all six expected slugs", () => {
    const slugs = caseStudies.map((cs) => cs.slug)
    expect(slugs).toContain("kitchen-meat")
    expect(slugs).toContain("nidemiel")
    expect(slugs).toContain("mizan")
    expect(slugs).toContain("haramain-prestige")
    expect(slugs).toContain("al-mumayiz")
    expect(slugs).toContain("e-shop-admin")
  })

  it("never carries an empty name, category or description", () => {
    for (const cs of caseStudies) {
      expect(cs.name.trim().length).toBeGreaterThan(0)
      expect(cs.category.trim().length).toBeGreaterThan(0)
      expect(cs.description.trim().length).toBeGreaterThan(10)
    }
  })

  it("never carries an empty seoTitle or seoDescription", () => {
    for (const cs of caseStudies) {
      expect(cs.seoTitle.trim().length).toBeGreaterThan(0)
      expect(cs.seoDescription.trim().length).toBeGreaterThan(20)
    }
  })

  it("keeps seoDescriptions under 160 characters", () => {
    for (const cs of caseStudies) {
      expect(cs.seoDescription.length).toBeLessThanOrEqual(160)
    }
  })

  it("exposes an imageSrc and a non-empty imageAlt for every study", () => {
    for (const cs of caseStudies) {
      expect(cs.imageSrc.startsWith("/")).toBe(true)
      expect(cs.imageAlt.trim().length).toBeGreaterThan(10)
    }
  })

  it("published studies have at least one tag", () => {
    for (const cs of caseStudies.filter((cs) => cs.status === "published")) {
      expect(cs.tags.length).toBeGreaterThan(0)
    }
  })

  it("href, when present, is an absolute HTTPS URL", () => {
    for (const cs of caseStudies) {
      if (cs.href !== undefined) {
        expect(cs.href.startsWith("https://")).toBe(true)
      }
    }
  })

  it("e-shop-admin has no href (internal demo project, not a public client site)", () => {
    const eShopAdmin = caseStudies.find((cs) => cs.id === "e-shop-admin")
    expect(eShopAdmin).toBeDefined()
    expect(eShopAdmin!.href).toBeUndefined()
  })

  it("haramain-prestige has no href (site not yet publicly authorised for linking)", () => {
    const haramain = caseStudies.find((cs) => cs.id === "haramain-prestige")
    expect(haramain).toBeDefined()
    expect(haramain!.href).toBeUndefined()
  })

  it("the two vitrine studies currently published are kitchen-meat and al-mumayiz", () => {
    const publishedVitrineSlugs = ["kitchen-meat", "al-mumayiz"]
    for (const slug of publishedVitrineSlugs) {
      const cs = caseStudies.find((cs) => cs.slug === slug)
      expect(cs).toBeDefined()
      expect(cs!.status).toBe("published")
    }
    // haramain-prestige is planned until client authorises publication
    const haramain = caseStudies.find((cs) => cs.slug === "haramain-prestige")
    expect(haramain!.status).toBe("planned")
  })
})
