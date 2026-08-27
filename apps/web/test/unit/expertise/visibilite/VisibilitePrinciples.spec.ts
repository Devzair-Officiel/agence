import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import VisibilitePrinciples from "~/components/expertise/visibilite/VisibilitePrinciples.vue"

const benefits = [
  {
    title: "Une visibilité durable",
    description:
      "Les gains obtenus par un travail SEO éditorial et technique se maintiennent dans le temps sans coût publicitaire récurrent.",
  },
  {
    title: "Un trafic qualifié",
    description:
      "Les contenus visent les requêtes correspondant à vos publics, pas les volumes flatteurs sans conversion.",
  },
  {
    title: "Une présence locale crédible",
    description:
      "La cohérence entre votre site, votre fiche d'établissement et vos citations locales renforce votre légitimité sur votre zone.",
  },
] as const

describe("VisibilitePrinciples", () => {
  it("expose l'eyebrow « Bénéfices concrets » attendu par le contrat éditorial", () => {
    const wrapper = mount(VisibilitePrinciples, { props: { benefits } })
    expect(wrapper.text()).toContain("Bénéfices concrets")
  })

  it("porte un H2 unique validé", () => {
    const wrapper = mount(VisibilitePrinciples, { props: { benefits } })
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Une visibilité qui tient, cible et rayonne localement.",
    )
  })

  it("rend un H3 par bénéfice avec le titre exact fourni par la config", () => {
    const wrapper = mount(VisibilitePrinciples, { props: { benefits } })
    const titles = wrapper.findAll("h3").map((n) => n.text())
    expect(titles).toEqual(benefits.map((b) => b.title))
  })

  it("rend chaque description associée verbatim", () => {
    const wrapper = mount(VisibilitePrinciples, { props: { benefits } })
    const text = wrapper.text()
    for (const b of benefits) expect(text).toContain(b.description)
  })

  it("mappe les trois labels narratifs Durée / Pertinence / Proximité aux bénéfices", () => {
    const wrapper = mount(VisibilitePrinciples, { props: { benefits } })
    const labels = wrapper
      .findAll(".visibilite-principles__item-label")
      .map((n) => n.text())
    expect(labels).toEqual(["Durée", "Pertinence", "Proximité"])
  })

  it("numérote les principes 01…03", () => {
    const wrapper = mount(VisibilitePrinciples, { props: { benefits } })
    const indexes = wrapper
      .findAll(".visibilite-principles__item-index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03"])
  })
})
