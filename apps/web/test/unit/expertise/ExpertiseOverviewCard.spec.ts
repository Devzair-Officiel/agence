import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ExpertiseOverviewCard from "~/components/expertise/ExpertiseOverviewCard.vue"
import { expertisePages } from "~/config/expertise-pages"
import { expertisePillars } from "~/config/expertise-pillars"

// NuxtLink n'est pas résolu dans l'environnement Vitest brut ; on le stubbe
// vers un simple <a href> qui préserve les assertions sur l'attribut href et
// sur le tagName du wrapper.
const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

const firstPillar = expertisePillars[0]!
const firstPage = expertisePages.find((p) => p.pillarId === firstPillar.id)!

describe("ExpertiseOverviewCard — sans définition de page (fallback narratif)", () => {
  it("rend un <article> quand aucune page n'est fournie", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar },
      global: { stubs: globalStubs },
    })
    expect(wrapper.element.tagName).toBe("ARTICLE")
    expect(wrapper.findAll("a")).toHaveLength(0)
  })

  it("rend un <article> quand la page correspondante a status 'planned'", () => {
    const plannedPage = { ...firstPage, status: "planned" as const }
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar, page: plannedPage },
      global: { stubs: globalStubs },
    })
    expect(wrapper.element.tagName).toBe("ARTICLE")
    expect(wrapper.findAll("a")).toHaveLength(0)
  })

  it("expose data-variant du pôle", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar },
      global: { stubs: globalStubs },
    })
    expect(wrapper.attributes("data-variant")).toBe(firstPillar.variant)
  })
})

describe("ExpertiseOverviewCard — avec page publiée (lien cliquable)", () => {
  it("rend un <a> pointant sur `page.route` quand status === 'published'", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar, page: firstPage },
      global: { stubs: globalStubs },
    })
    expect(wrapper.element.tagName).toBe("A")
    expect(wrapper.attributes("href")).toBe(firstPage.route)
  })

  it("conserve le H3 et n'introduit pas de H1/H2 secondaire", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar, page: firstPage },
      global: { stubs: globalStubs },
    })
    expect(wrapper.findAll("h1")).toHaveLength(0)
    expect(wrapper.findAll("h2")).toHaveLength(0)
    const h3 = wrapper.findAll("h3")
    expect(h3).toHaveLength(1)
    expect(h3[0]!.text()).toBe(firstPillar.label)
  })

  it("affiche un indice visuel « Découvrir ce pôle » aria-hidden", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar, page: firstPage },
      global: { stubs: globalStubs },
    })
    const cta = wrapper.get(".expertise-overview-card__cta")
    expect(cta.text()).toContain("Découvrir ce pôle")
    expect(cta.attributes("aria-hidden")).toBe("true")
  })
})

describe("ExpertiseOverviewCard — contenu narratif partagé", () => {
  it("rend la longDescription verbatim", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar },
      global: { stubs: globalStubs },
    })
    expect(wrapper.get(".expertise-overview-card__description").text()).toBe(
      firstPillar.longDescription,
    )
  })

  it("rend les trois services dans une <ul> sémantique", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar },
      global: { stubs: globalStubs },
    })
    const list = wrapper.get(".expertise-overview-card__services")
    expect(list.element.tagName).toBe("UL")
    const items = list.findAll("li")
    expect(items).toHaveLength(3)
    for (let i = 0; i < items.length; i += 1) {
      expect(items[i]!.text()).toBe(firstPillar.services[i])
    }
  })

  it("affiche l'ordre à deux chiffres marqué aria-hidden", () => {
    const secondPillar = expertisePillars[1]!
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: secondPillar },
      global: { stubs: globalStubs },
    })
    const order = wrapper.get(".expertise-overview-card__order")
    expect(order.text()).toBe("02")
    expect(order.attributes("aria-hidden")).toBe("true")
  })
})
