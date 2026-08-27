import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ValoriserContentKit from "~/components/expertise/valoriser/ValoriserContentKit.vue"

const deliverables = [
  "Photographies professionnelles adaptées à votre activité",
  "Textes rédigés pour vos pages et supports",
  "Structuration claire de votre offre",
  "Bibliothèque de contenus prêts à être utilisés",
  "Guide d'utilisation des contenus livrés",
] as const

describe("ValoriserContentKit", () => {
  it("expose l'eyebrow « Prestations » attendu par le contrat éditorial", () => {
    const wrapper = mount(ValoriserContentKit, {
      props: { deliverables },
    })
    expect(wrapper.text()).toContain("Prestations")
  })

  it("porte un unique H2 nommé « Un ensemble de contenus prêt à être utilisé. »", () => {
    const wrapper = mount(ValoriserContentKit, {
      props: { deliverables },
    })
    expect(wrapper.findAll("h2")).toHaveLength(1)
    expect(wrapper.get("h2").text()).toBe(
      "Un ensemble de contenus prêt à être utilisé.",
    )
  })

  it("rend un <ol role=list> contenant un module par livrable", () => {
    const wrapper = mount(ValoriserContentKit, {
      props: { deliverables },
    })
    const list = wrapper.get("ol")
    expect(list.attributes("role")).toBe("list")
    expect(list.findAll("li")).toHaveLength(deliverables.length)
  })

  it("rend chaque libellé de livrable verbatim", () => {
    const wrapper = mount(ValoriserContentKit, {
      props: { deliverables },
    })
    const rendered = wrapper.findAll(".valoriser-kit__label").map((n) => n.text())
    expect(rendered).toEqual([...deliverables])
  })

  it("numérote les livrables 01, 02, …", () => {
    const wrapper = mount(ValoriserContentKit, {
      props: { deliverables },
    })
    const indexes = wrapper
      .findAll(".valoriser-kit__index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03", "04", "05"])
  })

  it("mappe cinq tags éditoriaux Image / Rédaction / Mise en page / Réutilisable / Référence", () => {
    const wrapper = mount(ValoriserContentKit, {
      props: { deliverables },
    })
    const tags = wrapper.findAll(".valoriser-kit__tag").map((n) => n.text())
    expect(tags).toEqual([
      "Image",
      "Rédaction",
      "Mise en page",
      "Réutilisable",
      "Référence",
    ])
  })

  it("marque tous les décors SVG aria-hidden pour ne pas polluer l'AT", () => {
    const wrapper = mount(ValoriserContentKit, {
      props: { deliverables },
    })
    const decors = wrapper.findAll(".valoriser-kit__decor")
    expect(decors.length).toBe(deliverables.length)
    for (const decor of decors) {
      expect(decor.attributes("aria-hidden")).toBe("true")
    }
  })
})
