import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeFeaturedCaseStudy from "~/components/home/HomeFeaturedCaseStudy.vue"
import { caseStudies } from "~/config/case-studies"

describe("HomeFeaturedCaseStudy", () => {
  it("exposes the #realisations anchor on the section root", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const section = wrapper.get("section")
    expect(section.attributes("id")).toBe("realisations")
  })

  it("renders a single H2 with the exact editorial title", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Des solutions concrètes, pas seulement de belles interfaces.",
    )
  })

  it("carries the « Réalisations » eyebrow", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    expect(wrapper.text()).toContain("Réalisations")
  })

  it("renders one composition per configured case study", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const items = wrapper.findAll(".home-case__item")
    expect(items).toHaveLength(caseStudies.length)
  })

  it("uses semantic list markup (ul > li) for the case studies", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const list = wrapper.get(".home-case__list")
    expect(list.element.tagName).toBe("UL")
    expect(list.findAll(":scope > li")).toHaveLength(caseStudies.length)
  })

  it("renders the main project image with the configured alt (required, not decorative)", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    for (const study of caseStudies) {
      const img = wrapper.find(`img[src="${study.imageSrc}"]`)
      expect(img.exists()).toBe(true)
      expect(img.attributes("alt")).toBe(study.imageAlt)
      expect(img.attributes("alt")).not.toBe("")
    }
  })

  it("renders the decorative overlay with empty alt + aria-hidden when configured", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    for (const study of caseStudies) {
      if (!study.overlayImageSrc) continue
      const overlay = wrapper.find(`img[src="${study.overlayImageSrc}"]`)
      expect(overlay.exists()).toBe(true)
      // Purement décoratif : jamais annoncé par un lecteur d'écran.
      expect(overlay.attributes("alt")).toBe("")
      expect(overlay.attributes("aria-hidden")).toBe("true")
    }
  })

  it("renders each configured tag as a list item", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    for (const study of caseStudies) {
      const rendered = wrapper.findAll(".home-case__tag").map((n) => n.text())
      for (const tag of study.tags) {
        expect(rendered).toContain(tag)
      }
    }
  })

  it("renders the long editorial description of each project", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    for (const study of caseStudies) {
      expect(wrapper.text()).toContain(study.longDescription)
    }
  })

  it("does not render a « Voir le projet » CTA when `to` is not set (no dead link)", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    for (const study of caseStudies) {
      if (study.to) continue
      // Aucun lien vers une page qui n'existe pas — AGENTS.md rule 1.
      const anchors = wrapper.findAll("a")
      for (const a of anchors) {
        expect(a.attributes("href")).not.toBe("#")
      }
    }
  })

  it("carries no fictional client name or numeric proof (AGENTS.md rule 1)", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const text = wrapper.text()
    // Pas de pourcentage d'amélioration inventé (trafic, conversion, etc.).
    expect(text).not.toMatch(/\d+\s?%/)
    expect(text).not.toMatch(/témoignage/i)
  })

  // --- Chemins assets (WebP)

  it("all imageSrc paths point to .webp files", () => {
    for (const study of caseStudies) {
      expect(study.imageSrc).toMatch(/\.webp$/)
    }
  })

  it("all overlayImageSrc paths point to .webp files when present", () => {
    for (const study of caseStudies) {
      if (!study.overlayImageSrc) continue
      expect(study.overlayImageSrc).toMatch(/\.webp$/)
    }
  })

  it("no imageSrc or overlayImageSrc references a removed .png path", () => {
    for (const study of caseStudies) {
      expect(study.imageSrc).not.toMatch(/\.png$/)
      if (study.overlayImageSrc) {
        expect(study.overlayImageSrc).not.toMatch(/\.png$/)
      }
    }
  })

  // --- Priorité réseau : toutes les images utilisent loading=lazy
  // Le preload est géré par HomeFeaturedCaseStudy via new Image() pour
  // éviter tout conflit fetchpriority avec les ressources critiques en HTML SSR.

  it("all images in the carousel use loading=lazy", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const imgs = wrapper.findAll("img")
    for (const img of imgs) {
      expect(img.attributes("loading")).toBe("lazy")
      expect(img.attributes("fetchpriority")).toBeUndefined()
    }
  })
})
