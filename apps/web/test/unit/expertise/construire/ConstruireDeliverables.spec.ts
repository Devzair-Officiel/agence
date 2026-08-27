import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConstruireDeliverables from "~/components/expertise/construire/ConstruireDeliverables.vue"

const deliverables = [
  "Site vitrine responsive et accessible",
  "Plateforme e-commerce (catalogue, panier, commande)",
  "Application métier adaptée à vos processus",
  "Intégrations aux outils existants",
  "Documentation technique remise en fin de projet",
] as const

describe("ConstruireDeliverables", () => {
  it("expose l'eyebrow « Prestations » attendu par le contrat éditorial", () => {
    const wrapper = mount(ConstruireDeliverables, {
      props: { deliverables },
    })
    expect(wrapper.text()).toContain("Prestations")
  })

  it("porte un unique H2 nommé « Des briques qui composent un produit. »", () => {
    const wrapper = mount(ConstruireDeliverables, {
      props: { deliverables },
    })
    expect(wrapper.findAll("h2")).toHaveLength(1)
    expect(wrapper.get("h2").text()).toBe("Des briques qui composent un produit.")
  })

  it("rend un <ol role=list> contenant un item par livrable", () => {
    const wrapper = mount(ConstruireDeliverables, {
      props: { deliverables },
    })
    const list = wrapper.get("ol")
    expect(list.attributes("role")).toBe("list")
    expect(list.findAll("li")).toHaveLength(deliverables.length)
  })

  it("rend chaque libellé de livrable verbatim", () => {
    const wrapper = mount(ConstruireDeliverables, {
      props: { deliverables },
    })
    const rendered = wrapper.findAll(".construire-deliverables__label").map((n) => n.text())
    expect(rendered).toEqual([...deliverables])
  })

  it("numérote les livrables 01, 02, …", () => {
    const wrapper = mount(ConstruireDeliverables, {
      props: { deliverables },
    })
    const indexes = wrapper
      .findAll(".construire-deliverables__index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03", "04", "05"])
  })
})
