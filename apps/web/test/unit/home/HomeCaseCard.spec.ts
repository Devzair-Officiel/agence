import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeCaseCard from "~/components/home/HomeCaseCard.vue"
import { caseStudies } from "~/config/case-studies"

const nidemiel = caseStudies.find((s) => s.id === "nidemiel")!
const withOverlay = caseStudies.find((s) => !!s.overlayImageSrc)!
const withoutOverlay = { ...nidemiel, overlayImageSrc: undefined }

describe("HomeCaseCard", () => {
  // --- Rendu de base

  it("renders the main image with the study imageAlt", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: nidemiel } })
    const img = wrapper.find(".case-card__image")
    expect(img.exists()).toBe(true)
    expect(img.attributes("alt")).toBe(nidemiel.imageAlt)
  })

  it("renders the overlay img when overlayImageSrc is set", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: withOverlay } })
    const overlay = wrapper.find(".case-card__overlay-img")
    expect(overlay.exists()).toBe(true)
  })

  it("does not render the overlay span when overlayImageSrc is absent", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: withoutOverlay } })
    expect(wrapper.find(".case-card__overlay").exists()).toBe(false)
  })

  // --- Accessibilité de l'overlay

  it("overlay img has alt='' (decorative)", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: withOverlay } })
    const img = wrapper.find(".case-card__overlay-img")
    expect(img.attributes("alt")).toBe("")
  })

  it("overlay img has aria-hidden='true'", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: withOverlay } })
    const img = wrapper.find(".case-card__overlay-img")
    expect(img.attributes("aria-hidden")).toBe("true")
  })

  // --- Priorité réseau : toujours lazy (pas de fetchpriority=high en HTML SSR)

  it("main image always uses loading=lazy", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: nidemiel } })
    const mainImg = wrapper.find(".case-card__image")
    expect(mainImg.attributes("loading")).toBe("lazy")
    expect(mainImg.attributes("fetchpriority")).toBeUndefined()
  })

  it("overlay image always uses loading=lazy", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: withOverlay } })
    const overlayImg = wrapper.find(".case-card__overlay-img")
    expect(overlayImg.attributes("loading")).toBe("lazy")
    expect(overlayImg.attributes("fetchpriority")).toBeUndefined()
  })

  // --- Animation infinie supprimée

  it("overlay img has no infinite animation class or inline animation style", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: withOverlay } })
    const img = wrapper.find(".case-card__overlay-img")
    expect(img.attributes("style") ?? "").not.toContain("animation")
  })

  // --- Overlay toujours visible avec reduced-motion (SSR-fallback CSS)

  it("overlay span exists in the DOM regardless of revealed state", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: withOverlay } })
    expect(wrapper.find(".case-card__overlay").exists()).toBe(true)
  })

  it("does not add case-card--revealed class before intersection (initial render)", () => {
    const wrapper = mount(HomeCaseCard, { props: { study: nidemiel } })
    const root = wrapper.find(".case-card")
    expect(root.exists()).toBe(true)
  })
})
