import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConcevoirDecisionSection from "~/components/expertise/concevoir/ConcevoirDecisionSection.vue"

const props = {
  needTitle: "À qui cela s'adresse",
  needDescription: "Aux entreprises qui lancent un nouveau site.",
  approachTitle: "Comment nous procédons",
  approachDescription: "Nous partons de vos objectifs et de vos utilisateurs.",
}

describe("ConcevoirDecisionSection", () => {
  it("porte le H2 validé « Avant de construire, il faut décider. »", () => {
    const wrapper = mount(ConcevoirDecisionSection, { props })
    expect(wrapper.get("h2").text()).toBe(
      "Avant de construire, il faut décider.",
    )
  })

  it("rend les deux libellés courts À qui cela s'adresse / Notre approche", () => {
    const wrapper = mount(ConcevoirDecisionSection, { props })
    const text = wrapper.text()
    expect(text).toContain("À qui cela s'adresse")
    expect(text).toContain("Notre approche")
  })

  it("rend needTitle et approachTitle en H3 dans leurs cartes respectives", () => {
    const wrapper = mount(ConcevoirDecisionSection, { props })
    const titles = wrapper.findAll("h3").map((n) => n.text())
    expect(titles).toEqual([props.needTitle, props.approachTitle])
  })

  it("rend needDescription et approachDescription verbatim", () => {
    const wrapper = mount(ConcevoirDecisionSection, { props })
    const text = wrapper.text()
    expect(text).toContain(props.needDescription)
    expect(text).toContain(props.approachDescription)
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(ConcevoirDecisionSection, { props })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
