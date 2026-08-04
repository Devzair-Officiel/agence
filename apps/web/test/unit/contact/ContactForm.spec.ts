import { flushPromises, mount } from "@vue/test-utils"
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest"

import ContactForm from "~/components/contact/ContactForm.vue"

// Smoke test du composant orchestrateur :
//   - le form est bien un <form>, labellé par un titre sr-only ;
//   - il expose les champs attendus (name, email, message, consent, projectType) ;
//   - le honeypot `website` est présent, hors flux et aria-hidden ;
//   - le bouton d'envoi n'est désactivé QUE pendant l'envoi (DEV-045) —
//     un formulaire vide doit pouvoir être soumis pour déclencher les
//     messages de validation client, sans quoi l'utilisateur n'a aucun
//     retour explicite sur ce qui manque.

describe("ContactForm", () => {
  const stubs = {
    ClientOnly: { template: "<div><slot /></div>" },
    TurnstileWidget: {
      name: "TurnstileWidget",
      props: ["siteKey", "theme"],
      template: '<div data-test="turnstile" />',
    },
  }

  function mountForm(siteKey = "") {
    return mount(ContactForm, {
      props: { endpoint: "/api/contact", turnstileSiteKey: siteKey },
      global: { stubs },
    })
  }

  it("renders a <form> labelled by an sr-only title", () => {
    const wrapper = mountForm()
    const form = wrapper.get("form")
    expect(form.attributes("aria-labelledby")).toBeTruthy()
    expect(wrapper.find("h3.contact-form__sr-title").text()).toBe(
      "Formulaire de contact",
    )
  })

  it("exposes the expected fields with correct autocomplete", () => {
    const wrapper = mountForm()
    expect(wrapper.find('input[name="name"]').attributes("autocomplete")).toBe("name")
    expect(wrapper.find('input[name="email"]').attributes("autocomplete")).toBe("email")
    expect(wrapper.find('input[name="company"]').attributes("autocomplete")).toBe(
      "organization",
    )
    expect(wrapper.find('input[name="telephone"]').attributes("autocomplete")).toBe("tel")
    expect(wrapper.find('textarea[name="message"]').exists()).toBe(true)
    expect(wrapper.find('input[name="consent"]').attributes("type")).toBe("checkbox")
  })

  it("hosts the honeypot input hidden from AT and out of the tab order", () => {
    const wrapper = mountForm()
    const honeypot = wrapper.find('input[name="website"]')
    expect(honeypot.exists()).toBe(true)
    expect(honeypot.attributes("tabindex")).toBe("-1")
    expect(honeypot.attributes("autocomplete")).toBe("off")
    // Le conteneur du honeypot doit être aria-hidden.
    const container = wrapper.find(".contact-form__honeypot")
    expect(container.attributes("aria-hidden")).toBe("true")
  })

  it("keeps the submit button enabled at load — Turnstile is not a gating factor client-side", () => {
    const wrapper = mountForm()
    const submit = wrapper.get('button[type="submit"]')
    // DEV-045 : le bouton n'est plus grisé en attente de Turnstile. Le
    // token est contrôlé côté serveur (code `turnstile_rejected`). Un
    // formulaire vide doit pouvoir être envoyé pour déclencher les
    // messages de validation client — sinon l'utilisateur ne sait pas
    // pourquoi rien ne se passe.
    expect(submit.attributes("disabled")).toBeUndefined()
  })

  it("hides the interactive overlays (dot + consent box) from pointer events so the label handles clicks natively", () => {
    const wrapper = mountForm()
    // Les inputs natifs (radio + checkbox) sont visuellement masqués
    // (`position: absolute; opacity: 0`). Les overlays décoratifs
    // `.contact-form__project-dot` et `.contact-form__consent-box`
    // doivent être marqués `aria-hidden="true"` ET recevoir CSS
    // `pointer-events: none` (voir style scoped du composant) pour que
    // le clic tombant sur eux se propage au <label> parent, qui active
    // alors l'input natif. Ici on vérifie au moins la sémantique ARIA.
    const dot = wrapper.find(".contact-form__project-dot")
    expect(dot.exists()).toBe(true)
    expect(dot.attributes("aria-hidden")).toBe("true")

    const consentBox = wrapper.find(".contact-form__consent-box")
    expect(consentBox.exists()).toBe(true)
    expect(consentBox.attributes("aria-hidden")).toBe("true")
  })

  it("surfaces field validation errors when submitting an empty form (no API call)", async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response("{}", { status: 200 }),
    )
    const originalFetch = globalThis.fetch
    globalThis.fetch = fetchMock as unknown as typeof globalThis.fetch
    try {
      const wrapper = mountForm()
      await wrapper.get("form").trigger("submit")
      await flushPromises()

      // Aucun appel réseau — validation client bloque la soumission.
      expect(fetchMock).not.toHaveBeenCalled()

      // Les erreurs par champ (name, email, message, consent) sont exposées.
      const emailInput = wrapper.get('input[name="email"]').element as HTMLInputElement
      expect(emailInput.getAttribute("aria-invalid")).toBe("true")
      expect(wrapper.text()).toContain("Le nom est requis")
      expect(wrapper.text()).toContain("Le message doit contenir au moins 20 caractères")
      expect(wrapper.text()).toContain("Le consentement est requis")
    } finally {
      globalThis.fetch = originalFetch
    }
  })

  it("renders the five expected project-type radios", () => {
    const wrapper = mountForm()
    const values = wrapper
      .findAll('input[type="radio"][name="projectType"]')
      .map((r) => r.attributes("value"))
    expect(values).toEqual(["refonte", "creation", "seo", "audit", "autre"])
  })

  it("exposes explicit grid hooks for each short field and both full-width groups", () => {
    const wrapper = mountForm()
    for (const field of ["name", "email", "company", "telephone", "message"]) {
      expect(wrapper.find(`.contact-form__field--${field}`).exists()).toBe(true)
    }
    expect(wrapper.find(".contact-form__project").exists()).toBe(true)
    expect(wrapper.find(".contact-form__consent").exists()).toBe(true)
  })

  it("carries the RGPD privacy note in the aria-describedby chain", () => {
    const wrapper = mountForm()
    const describedBy = wrapper.get("form").attributes("aria-describedby")!
    expect(describedBy).toBeTruthy()
    const privacyNode = wrapper.find(`#${describedBy.split(" ")[0]}`)
    expect(privacyNode.text()).toContain("Base légale")
    expect(privacyNode.text()).toContain("RGPD")
  })

  describe("global error banner on HTTP 503", () => {
    let originalFetch: typeof globalThis.fetch | undefined

    beforeEach(() => {
      originalFetch = globalThis.fetch
    })

    afterEach(() => {
      if (originalFetch) {
        globalThis.fetch = originalFetch
      }
    })

    it("shows the verbatim temporary_error message and keeps values intact", async () => {
      const fetchMock = vi.fn().mockResolvedValue(
        new Response(
          JSON.stringify({
            status: "error",
            code: "temporary_error",
            request_id: "req-503-verbatim",
          }),
          { status: 503, headers: { "Content-Type": "application/json" } },
        ),
      )
      globalThis.fetch = fetchMock as unknown as typeof globalThis.fetch

      const wrapper = mountForm()

      // Injecte des valeurs valides + un token Turnstile pour débloquer submit.
      await wrapper.get('input[name="name"]').setValue("Alice Dupont")
      await wrapper.get('input[name="email"]').setValue("alice@example.com")
      await wrapper
        .get('textarea[name="message"]')
        .setValue(
          "Nous souhaitons refondre notre site vitrine pour clarifier notre offre.",
        )
      await wrapper.get('input[name="consent"]').setValue(true)
      await wrapper
        .get('input[value="refonte"]')
        .setValue()
      wrapper.findComponent({ name: "TurnstileWidget" }).vm.$emit("success", "cf-token-xyz")
      await flushPromises()

      await wrapper.get("form").trigger("submit")
      await flushPromises()

      const banner = wrapper.find(".contact-form__status")
      expect(banner.exists()).toBe(true)
      expect(banner.text()).toContain("Service momentanément indisponible")
      expect(banner.text()).toContain(
        "Le service est momentanément indisponible. Votre message n'a pas été envoyé. Merci de réessayer plus tard.",
      )
      expect(banner.text()).toContain("req-503-verbatim")

      // Contrat ADR-008 §7 : les valeurs ne doivent pas être effacées.
      expect(
        (wrapper.get('input[name="email"]').element as HTMLInputElement).value,
      ).toBe("alice@example.com")
      expect(
        (wrapper.get('textarea[name="message"]').element as HTMLTextAreaElement).value,
      ).toContain("refondre notre site vitrine")
    })
  })
})
