import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EditorialSection from "~/components/editorial/EditorialSection.vue"

describe("EditorialSection", () => {
  it("renders exactly one H2 with the provided title", () => {
    const wrapper = mount(EditorialSection, {
      props: { title: "Notre approche" },
    })
    const headings = wrapper.findAll("h2")
    expect(headings).toHaveLength(1)
    expect(headings[0]!.text()).toBe("Notre approche")
  })

  it("renders the intro paragraph when provided", () => {
    const wrapper = mount(EditorialSection, {
      props: { title: "Titre", intro: "Une introduction verbatim." },
    })
    expect(wrapper.get(".editorial-section__intro").text()).toBe(
      "Une introduction verbatim.",
    )
  })

  it("omits the intro paragraph when not provided", () => {
    const wrapper = mount(EditorialSection, { props: { title: "Titre" } })
    expect(wrapper.find(".editorial-section__intro").exists()).toBe(false)
  })

  it("renders the eyebrow when provided", () => {
    const wrapper = mount(EditorialSection, {
      props: { title: "Titre", eyebrow: "Section" },
    })
    expect(wrapper.get(".editorial-section__eyebrow").text()).toBe("Section")
  })

  it("applies data-tone from the tone prop", () => {
    const wrapper = mount(EditorialSection, {
      props: { title: "Titre", tone: "inverse" },
    })
    expect(wrapper.get("section").attributes("data-tone")).toBe("inverse")
  })

  it("defaults to data-tone=\"default\"", () => {
    const wrapper = mount(EditorialSection, { props: { title: "Titre" } })
    expect(wrapper.get("section").attributes("data-tone")).toBe("default")
  })

  it("uses aria-labelledby pointing at the H2 id", () => {
    const wrapper = mount(EditorialSection, { props: { title: "Titre" } })
    const section = wrapper.get("section")
    const h2Id = wrapper.get("h2").attributes("id")
    expect(section.attributes("aria-labelledby")).toBe(h2Id)
    expect(h2Id).toBeTruthy()
  })

  it("exposes the section id when sectionId is provided", () => {
    const wrapper = mount(EditorialSection, {
      props: { title: "Titre", sectionId: "custom-anchor" },
    })
    expect(wrapper.get("section").attributes("id")).toBe("custom-anchor")
  })

  it("renders the default slot content inside the body", () => {
    const wrapper = mount(EditorialSection, {
      props: { title: "Titre" },
      slots: { default: '<p data-test="body">contenu</p>' },
    })
    const body = wrapper.get(".editorial-section__body")
    expect(body.find('[data-test="body"]').exists()).toBe(true)
  })

  it("omits the body container when no slot content is provided", () => {
    const wrapper = mount(EditorialSection, { props: { title: "Titre" } })
    expect(wrapper.find(".editorial-section__body").exists()).toBe(false)
  })
})
