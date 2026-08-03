import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import BaseButton from "~/components/base/BaseButton.vue"

describe("EditorialCallout", () => {
  const baseProps = {
    title: "Découvrez nos expertises.",
    primary: { label: "Voir nos expertises", to: "/expertises" },
  }

  const mountCallout = (
    props: Record<string, unknown> = {},
  ) =>
    mount(EditorialCallout, {
      props: { ...baseProps, ...props },
      global: { components: { BaseButton } },
    })

  it("renders exactly one H2 with the provided title", () => {
    const wrapper = mountCallout()
    const headings = wrapper.findAll("h2")
    expect(headings).toHaveLength(1)
    expect(headings[0]!.text()).toBe(baseProps.title)
  })

  it("renders the primary CTA as a link with the provided route", () => {
    const wrapper = mountCallout()
    const primary = wrapper.get('a[href="/expertises"]')
    expect(primary.attributes("data-variant")).toBe("primary")
    expect(primary.text()).toContain("Voir nos expertises")
  })

  it("renders the secondary CTA when provided", () => {
    const wrapper = mountCallout({
      secondary: { label: "Contact", to: "/#contact", external: true },
    })
    const secondary = wrapper.get('a[href="/#contact"]')
    expect(secondary.attributes("data-variant")).toBe("secondary")
  })

  it("does not render a secondary CTA when omitted", () => {
    const wrapper = mountCallout()
    expect(wrapper.findAll(".base-button")).toHaveLength(1)
  })

  it("renders the eyebrow when provided", () => {
    const wrapper = mountCallout({ eyebrow: "Aller plus loin" })
    expect(wrapper.get(".editorial-callout__eyebrow").text()).toBe(
      "Aller plus loin",
    )
  })

  it("renders the description paragraph when provided", () => {
    const wrapper = mountCallout({ description: "Un pont éditorial court." })
    expect(wrapper.get(".editorial-callout__description").text()).toBe(
      "Un pont éditorial court.",
    )
  })

  it("applies data-tone from the tone prop", () => {
    const wrapper = mountCallout({ tone: "accent" })
    expect(wrapper.get("section").attributes("data-tone")).toBe("accent")
  })

  it("defaults to data-tone=\"inverse\"", () => {
    const wrapper = mountCallout()
    expect(wrapper.get("section").attributes("data-tone")).toBe("inverse")
  })

  it("uses aria-labelledby pointing at the H2 id", () => {
    const wrapper = mountCallout()
    const section = wrapper.get("section")
    const titleId = wrapper.get("h2").attributes("id")
    expect(section.attributes("aria-labelledby")).toBe(titleId)
    expect(titleId).toBeTruthy()
  })
})
