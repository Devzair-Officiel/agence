import { beforeEach, describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeCallToAction from "~/components/home/HomeCallToAction.vue"

// Depuis la Phase 6B, la section « Parlons de votre projet » héberge le vrai
// formulaire de contact. Les branches « mailto conditionnel » et « notice
// preprod » ont disparu (elles étaient un placeholder de Phase 5D). On garde ici
// une couverture unitaire ciblée : ancre, verbatim éditorial, présence du form.

const runtimeStub = {
  public: {
    siteIndexable: false as boolean | undefined,
    apiBaseUrl: "/api",
    turnstileSiteKey: "" as string,
  },
}

;(globalThis as unknown as { useRuntimeConfig: () => unknown }).useRuntimeConfig =
  () => runtimeStub

async function mountSection() {
  return mount(HomeCallToAction, {
    global: {
      stubs: {
        ContactForm: {
          name: "ContactForm",
          props: ["endpoint", "turnstileSiteKey"],
          template: '<div data-test="contact-form" :data-endpoint="endpoint" :data-turnstile="turnstileSiteKey" />',
        },
      },
    },
  })
}

describe("HomeCallToAction", () => {
  beforeEach(() => {
    runtimeStub.public.siteIndexable = false
    runtimeStub.public.apiBaseUrl = "/api"
    runtimeStub.public.turnstileSiteKey = ""
  })

  it("exposes the #contact anchor on the section root", async () => {
    const wrapper = await mountSection()
    expect(wrapper.get("section").attributes("id")).toBe("contact")
  })

  it("renders a single H2 with the exact editorial title", async () => {
    const wrapper = await mountSection()
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Construisons une présence digitale à la hauteur de votre entreprise.",
    )
  })

  it("carries the « Parlons de votre projet » eyebrow", async () => {
    const wrapper = await mountSection()
    expect(wrapper.text()).toContain("Parlons de votre projet")
  })

  it("publishes the editorial paragraph verbatim", async () => {
    const wrapper = await mountSection()
    expect(wrapper.text()).toContain(
      "Un premier échange nous permettra de comprendre votre besoin, de clarifier les priorités et de définir une direction adaptée à votre activité.",
    )
  })

  it("renders the ContactForm bound to the API endpoint", async () => {
    const wrapper = await mountSection()
    const form = wrapper.find('[data-test="contact-form"]')
    expect(form.exists()).toBe(true)
    expect(form.attributes("data-endpoint")).toBe("/api/contact")
  })

  it("propagates the Turnstile site key from runtimeConfig.public", async () => {
    runtimeStub.public.turnstileSiteKey = "0xAAAA_LIVE_KEY"
    const wrapper = await mountSection()
    const form = wrapper.find('[data-test="contact-form"]')
    expect(form.attributes("data-turnstile")).toBe("0xAAAA_LIVE_KEY")
  })

  it("trims any trailing slash from apiBaseUrl", async () => {
    runtimeStub.public.apiBaseUrl = "/api/"
    const wrapper = await mountSection()
    const form = wrapper.find('[data-test="contact-form"]')
    expect(form.attributes("data-endpoint")).toBe("/api/contact")
  })

  it("carries no fictional coordinates and no legacy mailto/tel link", async () => {
    const wrapper = await mountSection()
    const text = wrapper.text().toLowerCase()
    expect(text).not.toMatch(/@example\./)
    expect(text).not.toMatch(/lorem ipsum/)
    expect(text).not.toMatch(/john doe/)
    expect(text).not.toMatch(/avant la mise en production/)
    expect(wrapper.findAll('a[href^="mailto:"]')).toHaveLength(0)
    expect(wrapper.findAll('a[href^="tel:"]')).toHaveLength(0)
  })
})
