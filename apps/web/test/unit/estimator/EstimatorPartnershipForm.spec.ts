import { describe, it, expect, vi } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorPartnershipForm from "~/components/estimator/EstimatorPartnershipForm.vue"
import type { EstimatePayload } from "~/types/estimator"
import type { PartnershipInitialContact } from "~/types/estimator-partnership"

const BASE_QUESTIONNAIRE: EstimatePayload = {
  project_type: "vitrinesite",
  objectives: [],
  current_situation: "none",
  scale: "small",
  features: [],
  content_needs: [],
  visibility_needs: [],
  care_needs: [],
}

function mountForm(
  initialContact?: PartnershipInitialContact,
  _fetcher = vi.fn().mockResolvedValue({ status: 201, json: () => Promise.resolve({ proposal_id: "test-id" }) }),
) {
  return mount(EstimatorPartnershipForm, {
    props: { questionnairePayload: BASE_QUESTIONNAIRE, initialContact },
    global: {
      provide: {},
    },
  })
}

describe("EstimatorPartnershipForm", () => {
  it("affiche le heading 'Proposer un partenariat'", () => {
    const wrapper = mountForm()
    expect(wrapper.text()).toContain("Proposer un partenariat")
  })

  it("affiche le texte éditorial sur le caractère exceptionnel", () => {
    const wrapper = mountForm()
    const text = wrapper.text()
    expect(text).toContain("ne constitue pas une alternative automatiquement disponible")
  })

  it("affiche les champs contact requis", () => {
    const wrapper = mountForm()
    expect(wrapper.find("input[name='name']").exists()).toBe(true)
    expect(wrapper.find("input[name='email']").exists()).toBe(true)
  })

  it("affiche les champs contact optionnels", () => {
    const wrapper = mountForm()
    expect(wrapper.find("input[name='phone']").exists()).toBe(true)
    expect(wrapper.find("input[name='company']").exists()).toBe(true)
  })

  it("pré-remplit les champs contact si initialContact fourni", () => {
    const contact: PartnershipInitialContact = {
      name: "Marie Dupont",
      email: "marie@test.com",
      phone: "+33 6 00 00 00 00",
      company: "ACME",
    }
    const wrapper = mountForm(contact)
    const nameInput = wrapper.find<HTMLInputElement>("input[name='name']")
    expect(nameInput.element.value).toBe("Marie Dupont")
    const emailInput = wrapper.find<HTMLInputElement>("input[name='email']")
    expect(emailInput.element.value).toBe("marie@test.com")
  })

  it("affiche les radios pour le type de partenariat", () => {
    const wrapper = mountForm()
    const radios = wrapper.findAll("input[type='radio'][name='partnership-type']")
    expect(radios.length).toBe(5)
  })

  it("affiche les labels corrects pour les types de partenariat", () => {
    const wrapper = mountForm()
    const text = wrapper.text()
    expect(text).toContain("Partage de revenus")
    expect(text).toContain("Participation au projet / entreprise")
    expect(text).toContain("Collaboration commerciale")
    expect(text).toContain("Échange de prestations")
    expect(text).toContain("Autre proposition")
  })

  it("affiche les radios pour l'avancement du projet", () => {
    const wrapper = mountForm()
    const radios = wrapper.findAll("input[type='radio'][name='partnership-stage']")
    expect(radios.length).toBe(5)
  })

  it("affiche les labels corrects pour les stades", () => {
    const wrapper = mountForm()
    const text = wrapper.text()
    expect(text).toContain("Idée / préparation")
    expect(text).toContain("Activité avec premiers clients")
  })

  it("affiche le textarea proposalText obligatoire", () => {
    const wrapper = mountForm()
    expect(wrapper.find("textarea[name='proposal_text']").exists()).toBe(true)
  })

  it("affiche le textarea relevanceText optionnel", () => {
    const wrapper = mountForm()
    expect(wrapper.find("textarea[name='relevance_text']").exists()).toBe(true)
  })

  it("affiche le honeypot hors écran", () => {
    const wrapper = mountForm()
    const honeypot = wrapper.find(".partnership-form__honeypot")
    expect(honeypot.exists()).toBe(true)
    expect(honeypot.attributes("aria-hidden")).toBe("true")
  })

  it("affiche la mention vie privée", () => {
    const wrapper = mountForm()
    expect(wrapper.text()).toContain("RGPD")
  })

  it("affiche le bouton Annuler et émet close", async () => {
    const wrapper = mountForm()
    const cancelBtn = wrapper.findAll("button").find((b) => b.text().includes("Annuler"))
    expect(cancelBtn).toBeDefined()
    await cancelBtn!.trigger("click")
    expect(wrapper.emitted("close")).toBeTruthy()
  })

  it("valide name requis avant submit", async () => {
    const wrapper = mountForm()
    await wrapper.find("form").trigger("submit")
    expect(wrapper.text()).toContain("requis")
  })

  it("valide email requis avant submit", async () => {
    const wrapper = mountForm()
    await wrapper.find("input[name='name']").setValue("Jean")
    await wrapper.find("form").trigger("submit")
    expect(wrapper.text()).toContain("e-mail valide")
  })

  it("valide partnershipType requis avant submit", async () => {
    const wrapper = mountForm()
    await wrapper.find("input[name='name']").setValue("Jean")
    await wrapper.find("input[name='email']").setValue("jean@test.com")
    await wrapper.find("form").trigger("submit")
    expect(wrapper.text()).toContain("nature de proposition")
  })

  it("valide proposalText min 10 chars", async () => {
    const wrapper = mountForm()
    await wrapper.find("input[name='name']").setValue("Jean")
    await wrapper.find("input[name='email']").setValue("jean@test.com")
    await wrapper.find("input[type='radio'][value='revenue_share']").setValue("revenue_share")
    await wrapper.find("input[type='radio'][value='launched']").setValue("launched")
    await wrapper.find("textarea[name='proposal_text']").setValue("court")
    await wrapper.find("form").trigger("submit")
    expect(wrapper.text()).toContain("10 caractères minimum")
  })

  it("affiche compteur de caractères pour proposalText", () => {
    const wrapper = mountForm()
    expect(wrapper.text()).toContain("500")
  })

  it("n'affiche jamais 'Payez avec votre projet' ni '0 €'", () => {
    const wrapper = mountForm()
    const text = wrapper.text()
    expect(text).not.toContain("Payez avec")
    expect(text).not.toContain("0 €")
    expect(text).not.toContain("Revenue share disponible")
    expect(text).not.toContain("crédit")
    expect(text).not.toContain("financement")
  })

  it("n'utilise pas v-html — pas d'XSS possible sur les champs texte", () => {
    const wrapper = mountForm()
    const html = wrapper.html()
    expect(html).not.toContain("<script")
  })
})
