import { describe, expect, it } from "vitest"
import { expertisePages } from "~/config/expertise-pages"
import { expertisePillars } from "~/config/expertise-pillars"

describe("expertisePages", () => {
  it("declares exactly five definitions", () => {
    expect(expertisePages).toHaveLength(5)
  })

  it("uses unique ids and slugs", () => {
    const ids = expertisePages.map((p) => p.id)
    const slugs = expertisePages.map((p) => p.slug)
    expect(new Set(ids).size).toBe(ids.length)
    expect(new Set(slugs).size).toBe(slugs.length)
  })

  it("references an existing pillar for each definition", () => {
    const pillarIds = new Set(expertisePillars.map((p) => p.id))
    for (const page of expertisePages) {
      expect(pillarIds.has(page.pillarId)).toBe(true)
    }
  })

  it("keeps id equal to pillarId (stable one-to-one mapping)", () => {
    for (const page of expertisePages) {
      expect(page.id).toBe(page.pillarId)
    }
  })

  it("builds routes as `/expertises/{slug}` with a leading slash", () => {
    for (const page of expertisePages) {
      expect(page.route).toBe(`/expertises/${page.slug}`)
      expect(page.route.startsWith("/")).toBe(true)
    }
  })

  it("uses only lowercase URL-safe slugs (letters, digits, hyphens)", () => {
    const slugPattern = /^[a-z0-9]+(-[a-z0-9]+)*$/
    for (const page of expertisePages) {
      expect(page.slug).toMatch(slugPattern)
    }
  })

  it("never carries an empty title, shortTitle or summary", () => {
    for (const page of expertisePages) {
      expect(page.title.trim().length).toBeGreaterThan(0)
      expect(page.shortTitle.trim().length).toBeGreaterThan(0)
      expect(page.summary.trim().length).toBeGreaterThan(20)
    }
  })

  it("exposes exactly three non-empty services per page", () => {
    for (const page of expertisePages) {
      expect(page.services).toHaveLength(3)
      for (const service of page.services) {
        expect(service.trim().length).toBeGreaterThan(0)
      }
    }
  })

  it("marks every Phase 7B definition as `published`", () => {
    for (const page of expertisePages) {
      expect(page.status).toBe("published")
    }
  })

  it("never self-references in relatedPillarIds", () => {
    for (const page of expertisePages) {
      expect(page.relatedPillarIds).not.toContain(page.pillarId)
    }
  })

  it("exposes exactly two related pillars per page", () => {
    for (const page of expertisePages) {
      expect(page.relatedPillarIds).toHaveLength(2)
    }
  })

  it("points relatedPillarIds only at existing pillars", () => {
    const pillarIds = new Set(expertisePillars.map((p) => p.id))
    for (const page of expertisePages) {
      for (const relatedId of page.relatedPillarIds) {
        expect(pillarIds.has(relatedId)).toBe(true)
      }
    }
  })

  it("uses unique relatedPillarIds within a single page", () => {
    for (const page of expertisePages) {
      const unique = new Set(page.relatedPillarIds)
      expect(unique.size).toBe(page.relatedPillarIds.length)
    }
  })

  it("carries a non-empty introduction, eyebrow, seoTitle and seoDescription", () => {
    for (const page of expertisePages) {
      expect(page.eyebrow.trim().length).toBeGreaterThan(0)
      expect(page.introduction.trim().length).toBeGreaterThan(40)
      expect(page.seoTitle.trim().length).toBeGreaterThan(0)
      expect(page.seoDescription.trim().length).toBeGreaterThanOrEqual(120)
      expect(page.seoDescription.trim().length).toBeLessThanOrEqual(180)
    }
  })

  it("carries non-empty need and approach sections", () => {
    for (const page of expertisePages) {
      expect(page.needTitle.trim().length).toBeGreaterThan(0)
      expect(page.needDescription.trim().length).toBeGreaterThan(60)
      expect(page.approachTitle.trim().length).toBeGreaterThan(0)
      expect(page.approachDescription.trim().length).toBeGreaterThan(60)
    }
  })

  it("declares between three and six deliverables per page, all non-empty", () => {
    for (const page of expertisePages) {
      expect(page.deliverables.length).toBeGreaterThanOrEqual(3)
      expect(page.deliverables.length).toBeLessThanOrEqual(6)
      for (const deliverable of page.deliverables) {
        expect(deliverable.trim().length).toBeGreaterThan(0)
      }
    }
  })

  it("declares between three and five benefits per page with title and description", () => {
    for (const page of expertisePages) {
      expect(page.benefits.length).toBeGreaterThanOrEqual(3)
      expect(page.benefits.length).toBeLessThanOrEqual(5)
      for (const benefit of page.benefits) {
        expect(benefit.title.trim().length).toBeGreaterThan(0)
        expect(benefit.description.trim().length).toBeGreaterThan(20)
      }
    }
  })

  it("mirrors the services declared on the corresponding pillar", () => {
    for (const page of expertisePages) {
      const pillar = expertisePillars.find((p) => p.id === page.pillarId)
      expect(pillar).toBeDefined()
      expect(page.services).toEqual(pillar!.services)
    }
  })

  it("preserves the same ordering as expertisePillars", () => {
    const pageIds = expertisePages.map((p) => p.id)
    const pillarIds = expertisePillars.map((p) => p.id)
    expect(pageIds).toEqual(pillarIds)
  })
})
