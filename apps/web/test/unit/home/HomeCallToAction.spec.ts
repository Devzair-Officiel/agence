import { beforeEach, describe, expect, it, vi } from "vitest"
import { mount } from "@vue/test-utils"
import HomeCallToAction from "~/components/home/HomeCallToAction.vue"

// Contrôle stratégie de contact conditionnelle :
//   - site.contact.email == null  → aucun bouton, aucun mailto
//   - site.contact.email set      → BaseButton mailto avec href correct
//   - siteIndexable === false + contact null → indicateur de preprod visible
//   - siteIndexable === true  + contact null → aucun indicateur (production)
//
// On utilise `vi.mock` pour piloter les deux dépendances runtime : la
// config `~/config/site` (email nul/set) et `useRuntimeConfig` (indexable).

const runtimeStub = { public: { siteIndexable: false as boolean | undefined } }

vi.mock("~/config/site", () => ({
  site: {
    name: "Devzair",
    contact: { email: null as string | null, phone: null, city: null },
  },
}))

// On surcharge le stub global installé par test/setup.ts.
;(globalThis as unknown as { useRuntimeConfig: () => unknown }).useRuntimeConfig =
  () => runtimeStub

async function mountWith(options: {
  email?: string | null
  indexable?: boolean
}) {
  const { site } = await import("~/config/site")
  ;(site.contact as { email: string | null }).email = options.email ?? null
  runtimeStub.public.siteIndexable = options.indexable ?? false
  return mount(HomeCallToAction)
}

describe("HomeCallToAction", () => {
  beforeEach(async () => {
    const { site } = await import("~/config/site")
    ;(site.contact as { email: string | null }).email = null
    runtimeStub.public.siteIndexable = false
  })

  it("exposes the #contact anchor on the section root", async () => {
    const wrapper = await mountWith({})
    const section = wrapper.get("section")
    expect(section.attributes("id")).toBe("contact")
  })

  it("renders a single H2 with the exact editorial title", async () => {
    const wrapper = await mountWith({})
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Construisons une présence digitale à la hauteur de votre entreprise.",
    )
  })

  it("carries the « Parlons de votre projet » eyebrow", async () => {
    const wrapper = await mountWith({})
    expect(wrapper.text()).toContain("Parlons de votre projet")
  })

  it("publishes the editorial paragraph verbatim", async () => {
    const wrapper = await mountWith({})
    expect(wrapper.text()).toContain(
      "Un premier échange nous permettra de comprendre votre besoin, de clarifier les priorités et de définir une direction adaptée à votre activité.",
    )
  })

  it("renders no mailto link, no button, no href=# when contact email is null", async () => {
    const wrapper = await mountWith({ email: null })
    expect(wrapper.findAll('a[href^="mailto:"]')).toHaveLength(0)
    expect(wrapper.findAll("button")).toHaveLength(0)
    expect(wrapper.findAll('a[href="#"]')).toHaveLength(0)
  })

  it("renders a « Nous écrire » mailto BaseButton when contact email is set", async () => {
    const wrapper = await mountWith({ email: "hello@devzair.fr", indexable: true })
    const anchors = wrapper.findAll('a[href^="mailto:"]')
    expect(anchors).toHaveLength(1)
    expect(anchors[0]!.attributes("href")).toBe("mailto:hello@devzair.fr")
    expect(anchors[0]!.text()).toContain("Nous écrire")
  })

  it("shows the preprod notice only when non-indexable AND contact null", async () => {
    const wrapper = await mountWith({ email: null, indexable: false })
    expect(wrapper.text()).toContain(
      "Le moyen de contact en ligne sera activé avant la mise en production.",
    )
  })

  it("hides the preprod notice in production when contact is still null", async () => {
    const wrapper = await mountWith({ email: null, indexable: true })
    expect(wrapper.text()).not.toContain("avant la mise en production")
  })

  it("hides the preprod notice when contact email is set (even in preprod)", async () => {
    const wrapper = await mountWith({ email: "hello@devzair.fr", indexable: false })
    expect(wrapper.text()).not.toContain("avant la mise en production")
  })

  it("does not carry any fictional coordinates or fake copy", async () => {
    const wrapper = await mountWith({})
    const text = wrapper.text().toLowerCase()
    expect(text).not.toMatch(/@example\./)
    expect(text).not.toMatch(/lorem ipsum/)
    expect(text).not.toMatch(/john doe/)
    expect(wrapper.findAll('a[href^="tel:"]')).toHaveLength(0)
  })
})
