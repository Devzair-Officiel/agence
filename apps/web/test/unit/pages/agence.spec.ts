import { beforeEach, describe, expect, it, vi } from "vitest"
import { mount } from "@vue/test-utils"
import AgencePage from "~/pages/agence.vue"

// Capture les appels au composable SEO auto-importé pour vérifier que la page
// publie bien ses métadonnées via `usePageSeo` (pas via useSeoMeta/useHead
// manuels). On stubbe minimalement au niveau global.

interface SeoCall {
  title: string
  description: string
  path: string
  type?: string
}

const seoCalls: SeoCall[] = []

;(globalThis as unknown as { usePageSeo: (input: SeoCall) => void }).usePageSeo =
  vi.fn((input: SeoCall) => {
    seoCalls.push(input)
  })

describe("/agence page", () => {
  beforeEach(() => {
    seoCalls.length = 0
  })

  it("renders exactly one H1 with the editorial title verbatim", () => {
    const wrapper = mount(AgencePage)
    const headings = wrapper.findAll("h1")
    expect(headings).toHaveLength(1)
    expect(headings[0]!.text()).toBe(
      "Une agence digitale à taille humaine, pensée pour accompagner les entreprises dans leur globalité.",
    )
  })

  it("carries the « L'agence » eyebrow", () => {
    const wrapper = mount(AgencePage)
    expect(wrapper.text()).toContain("L'agence")
  })

  it("publishes the introduction verbatim", () => {
    const wrapper = mount(AgencePage)
    expect(wrapper.text()).toContain(
      "Devzair réunit stratégie, design, développement, contenus et visibilité afin de construire des solutions digitales cohérentes, utiles et évolutives.",
    )
  })

  it("publishes the exact SEO title and description through usePageSeo", () => {
    mount(AgencePage)
    expect(seoCalls).toHaveLength(1)
    expect(seoCalls[0]).toMatchObject({
      title: "Agence digitale à taille humaine",
      description:
        "Découvrez l'approche Devzair : une agence digitale à taille humaine qui réunit stratégie, design, développement, contenus et SEO autour de votre projet.",
      path: "/agence",
      type: "website",
    })
  })

  it("links to `/expertises` and to the `/#contact` anchor", () => {
    const wrapper = mount(AgencePage)
    const hrefs = wrapper.findAll("a").map((a) => a.attributes("href"))
    expect(hrefs).toContain("/expertises")
    expect(hrefs).toContain("/#contact")
  })

  it("never publishes fictional data (org chart, fake testimonials, invented numbers)", () => {
    const wrapper = mount(AgencePage)
    const text = wrapper.text().toLowerCase()
    // Formulations de contrôle : la page /agence ne doit pas prétendre à un
    // effectif chiffré, un organigramme, une fondation ou une localisation
    // qui n'a pas été validée par le client.
    expect(text).not.toMatch(/lorem ipsum/)
    expect(text).not.toMatch(/john doe/)
    expect(text).not.toMatch(/@example\./)
    expect(text).not.toMatch(/fondée en \d{4}/)
    expect(text).not.toMatch(/plus de \d+ (clients|projets|années)/)
  })

  it("uses only real routes on internal links (no dead expertise sub-routes)", () => {
    const wrapper = mount(AgencePage)
    const hrefs = wrapper.findAll("a").map((a) => a.attributes("href") ?? "")
    for (const href of hrefs) {
      // Aucun lien vers `/expertises/{slug}` — ces routes sont en Phase 7B.
      expect(href).not.toMatch(/^\/expertises\/[a-z]/)
      // Aucun lien vers `/realisations`, `/methode`, `/ressources` (non livrés).
      expect(href).not.toBe("/methode")
      expect(href).not.toBe("/realisations")
      expect(href).not.toBe("/ressources")
    }
  })
})
