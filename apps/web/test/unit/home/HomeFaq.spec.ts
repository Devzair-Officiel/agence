import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import HomeFaq from "~/components/home/HomeFaq.vue"

describe("HomeFaq", () => {
  it("exposes the #faq anchor and a single labelled H2", () => {
    const wrapper = mount(HomeFaq)
    const section = wrapper.get("section")
    const headings = wrapper.findAll("h2")

    expect(section.attributes("id")).toBe("faq")
    expect(section.attributes("aria-labelledby")).toBe("home-faq-title")
    expect(headings).toHaveLength(1)
    expect(headings[0]!.attributes("id")).toBe("home-faq-title")
    expect(headings[0]!.text().replace(/\s+/g, " ")).toBe(
      "Vos questions, nos réponses.",
    )
  })

  it("renders the seven supplied questions as native disclosure controls", () => {
    const wrapper = mount(HomeFaq)
    const items = wrapper.findAll("details.home-faq__item")
    const summaries = wrapper.findAll("summary.home-faq__summary")
    const questions = wrapper.findAll(".home-faq__question")

    expect(items).toHaveLength(7)
    expect(summaries).toHaveLength(7)
    expect(questions.map((question) => question.text())).toEqual([
      "Quels types de projets pouvez-vous accompagner ?",
      "Pouvez-vous prendre en charge un projet digital dans sa globalité ?",
      "Comment se déroule un projet avec Devzair ?",
      "Proposez-vous des solutions entièrement sur mesure ?",
      "Le référencement naturel est-il pris en compte lors de la création d’un site ?",
      "Comment sont déterminés le budget et le délai d’un projet ?",
      "Pouvez-vous continuer à nous accompagner après la mise en ligne ?",
    ])
  })

  it("groups the disclosures and opens only the first answer by default", () => {
    const wrapper = mount(HomeFaq)
    const items = wrapper.findAll("details.home-faq__item")

    expect(items.every((item) => item.attributes("name") === "home-faq")).toBe(
      true,
    )
    expect(items[0]!.attributes()).toHaveProperty("open")
    expect(items.slice(1).every((item) => !("open" in item.attributes()))).toBe(
      true,
    )
  })

  it("keeps both paragraphs of every answer in the SSR-friendly DOM", () => {
    const wrapper = mount(HomeFaq)
    const answers = wrapper.findAll(".home-faq__answer")

    expect(answers).toHaveLength(7)
    for (const answer of answers) {
      expect(answer.findAll("p")).toHaveLength(2)
    }
    expect(wrapper.text()).toContain(
      "Notre approche consiste à réunir les compétences réellement utiles au projet plutôt qu’à proposer une solution standardisée.",
    )
    expect(wrapper.text()).toContain(
      "L’objectif est de conserver une solution fiable et de pouvoir la faire évoluer avec l’activité de l’entreprise.",
    )
  })
})
