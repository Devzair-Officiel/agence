import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConcevoirDeliverablesBoard from "~/components/expertise/concevoir/ConcevoirDeliverablesBoard.vue"

const deliverables = [
  "Cadrage des objectifs et des utilisateurs",
  "Architecture d'information et arborescence",
  "Système de design (typographies, couleurs, composants)",
  "Maquettes d'interface responsive",
  "Guide d'usage remis à l'équipe",
] as const

describe("ConcevoirDeliverablesBoard", () => {
  it("expose l'eyebrow « Prestations » attendu par le contrat éditorial", () => {
    const wrapper = mount(ConcevoirDeliverablesBoard, {
      props: { deliverables },
    })
    expect(wrapper.text()).toContain("Prestations")
  })

  it("porte un unique H2 nommé", () => {
    const wrapper = mount(ConcevoirDeliverablesBoard, {
      props: { deliverables },
    })
    expect(wrapper.findAll("h2")).toHaveLength(1)
    expect(wrapper.get("h2").text()).toBe(
      "Les livrables associés à ce pôle.",
    )
  })

  it("rend un <ol role=list> contenant un item par livrable", () => {
    const wrapper = mount(ConcevoirDeliverablesBoard, {
      props: { deliverables },
    })
    const list = wrapper.get("ol")
    expect(list.attributes("role")).toBe("list")
    expect(list.findAll("li")).toHaveLength(deliverables.length)
  })

  it("rend chaque libellé de livrable verbatim", () => {
    const wrapper = mount(ConcevoirDeliverablesBoard, {
      props: { deliverables },
    })
    const rendered = wrapper.findAll(".concevoir-deliverables__label").map((n) => n.text())
    expect(rendered).toEqual([...deliverables])
  })

  it("numérote les livrables 01, 02, …", () => {
    const wrapper = mount(ConcevoirDeliverablesBoard, {
      props: { deliverables },
    })
    const indexes = wrapper
      .findAll(".concevoir-deliverables__index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03", "04", "05"])
  })
})
