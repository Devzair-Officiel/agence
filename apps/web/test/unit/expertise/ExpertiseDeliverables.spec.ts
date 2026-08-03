import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ExpertiseDeliverables from "~/components/expertise/ExpertiseDeliverables.vue"

describe("ExpertiseDeliverables", () => {
  const deliverables = [
    "Cadrage des objectifs",
    "Architecture d'information",
    "Système de design",
    "Maquettes responsive",
  ]

  it("rend une <ul role=\"list\">", () => {
    const wrapper = mount(ExpertiseDeliverables, { props: { deliverables } })
    const list = wrapper.get("ul")
    expect(list.attributes("role")).toBe("list")
  })

  it("rend exactement un <li> par livrable", () => {
    const wrapper = mount(ExpertiseDeliverables, { props: { deliverables } })
    const items = wrapper.findAll("li")
    expect(items).toHaveLength(deliverables.length)
    for (let i = 0; i < deliverables.length; i += 1) {
      expect(items[i]!.text()).toContain(deliverables[i]!)
    }
  })

  it("masque les puces décoratives aux lecteurs d'écran", () => {
    const wrapper = mount(ExpertiseDeliverables, { props: { deliverables } })
    const markers = wrapper.findAll(".expertise-deliverables__marker")
    expect(markers.length).toBeGreaterThan(0)
    for (const marker of markers) {
      expect(marker.attributes("aria-hidden")).toBe("true")
    }
  })
})
