import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"

import ContactForm from "~/components/contact/ContactForm.vue"

// Smoke test du composant orchestrateur :
//   - le form est bien un <form>, labellé par un titre sr-only ;
//   - il expose les champs attendus (name, email, message, consent, projectType) ;
//   - le honeypot `website` est présent, hors flux et aria-hidden ;
//   - le bouton d'envoi est désactivé tant qu'aucun token Turnstile n'est reçu.

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

  it("keeps the submit button disabled until a Turnstile token arrives", () => {
    const wrapper = mountForm()
    const submit = wrapper.get('button[type="submit"]')
    // Le composant Turnstile n'émet pas dans ce test : token reste null.
    expect(submit.attributes("disabled")).toBeDefined()
  })

  it("renders the five expected project-type radios", () => {
    const wrapper = mountForm()
    const values = wrapper
      .findAll('input[type="radio"][name="projectType"]')
      .map((r) => r.attributes("value"))
    expect(values).toEqual(["refonte", "creation", "seo", "audit", "autre"])
  })

  it("carries the RGPD privacy note in the aria-describedby chain", () => {
    const wrapper = mountForm()
    const describedBy = wrapper.get("form").attributes("aria-describedby")!
    expect(describedBy).toBeTruthy()
    const privacyNode = wrapper.find(`#${describedBy.split(" ")[0]}`)
    expect(privacyNode.text()).toContain("Base légale")
    expect(privacyNode.text()).toContain("RGPD")
  })
})
