import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeCallToAction from "~/components/home/HomeCallToAction.vue"

// Depuis le split du formulaire vers la page dédiée `/contact`, cette section
// n'est plus qu'un panneau CTA compact : eyebrow, H2, paragraphe, bouton
// « Parler de votre projet » pointant sur `/contact`. Aucun formulaire, plus
// aucun runtimeConfig consommé ici.

function mountSection() {
  return mount(HomeCallToAction, {
    global: {
      stubs: {
        BaseButton: {
          name: "BaseButton",
          props: ["to", "variant"],
          template:
            '<a class="base-button" :href="to" :data-variant="variant"><slot /><slot name="icon" /></a>',
        },
      },
    },
  })
}

describe("HomeCallToAction", () => {
  it("exposes the #contact anchor on the section root", () => {
    const wrapper = mountSection()
    expect(wrapper.get("section").attributes("id")).toBe("contact")
  })

  it("renders a single H2 with the exact editorial title", () => {
    const wrapper = mountSection()
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Construisons une présence digitale à la hauteur de votre entreprise.",
    )
  })

  it("carries the « Parlons de votre projet » eyebrow", () => {
    const wrapper = mountSection()
    expect(wrapper.text()).toContain("Parlons de votre projet")
  })

  it("publishes the editorial paragraph verbatim", () => {
    const wrapper = mountSection()
    expect(wrapper.text()).toContain(
      "Un premier échange nous permettra de comprendre votre besoin, de clarifier les priorités et de définir une direction adaptée à votre activité.",
    )
  })

  it("renders a single CTA button pointing to /contact", () => {
    const wrapper = mountSection()
    const buttons = wrapper.findAll(".home-cta__actions .base-button")
    expect(buttons).toHaveLength(1)
    expect(buttons[0]!.attributes("href")).toBe("/contact")
    expect(buttons[0]!.text()).toContain("Parler de votre projet")
  })

  it("no longer embeds the contact form nor any fictional coordinate", () => {
    const wrapper = mountSection()
    expect(wrapper.find("form.contact-form").exists()).toBe(false)
    const text = wrapper.text().toLowerCase()
    expect(text).not.toMatch(/@example\./)
    expect(text).not.toMatch(/lorem ipsum/)
    expect(text).not.toMatch(/john doe/)
    expect(wrapper.findAll('a[href^="mailto:"]')).toHaveLength(0)
    expect(wrapper.findAll('a[href^="tel:"]')).toHaveLength(0)
  })
})
