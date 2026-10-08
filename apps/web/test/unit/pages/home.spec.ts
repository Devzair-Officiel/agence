import { beforeEach, describe, expect, it, vi } from "vitest"
import { mount } from "@vue/test-utils"
import HomePage from "~/pages/index.vue"
import { site } from "~/config/site"

// Même approche que agence.spec.ts : on capture l'appel à usePageSeo pour
// vérifier le contrat SEO de l'accueil sans dispatch Nuxt réel.

interface SeoCall {
  title: string
  absoluteTitle?: boolean
  description: string
  path: string
  type?: string
}

const seoCalls: SeoCall[] = []

;(globalThis as unknown as { usePageSeo: (input: SeoCall) => void }).usePageSeo =
  vi.fn((input: SeoCall) => {
    seoCalls.push(input)
  })

describe("page d'accueil", () => {
  beforeEach(() => {
    seoCalls.length = 0
  })

  it("rend un seul H1", () => {
    const wrapper = mount(HomePage)
    expect(wrapper.findAll("h1")).toHaveLength(1)
  })

  it("présente Devzair explicitement dans l'introduction du premier écran", () => {
    const wrapper = mount(HomePage)
    expect(wrapper.get(".home-hero__lead").text()).toMatch(
      /^Devzair est une agence digitale/,
    )
  })

  it("publie un titre absolu qui porte la marque une seule fois", () => {
    mount(HomePage)
    expect(seoCalls).toHaveLength(1)
    const seo = seoCalls[0]!
    expect(seo).toMatchObject({
      title: "Devzair — Agence digitale : sites web, applications et SEO",
      absoluteTitle: true,
      path: "/",
      type: "website",
    })
    // Le template global n'est pas appliqué : le titre final est `seo.title`.
    expect(seo.title.match(/Devzair/g)).toHaveLength(1)
    expect(seo.title.startsWith(site.name)).toBe(true)
    expect(seo.title.length).toBeLessThanOrEqual(60)
  })
})
