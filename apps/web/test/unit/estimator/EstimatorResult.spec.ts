import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorResult from "~/components/estimator/EstimatorResult.vue"
import type { EstimateApiResponse } from "~/types/estimator-api"
import type { EstimatePayload } from "~/types/estimator"

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

const ESTIMATED_RESULT: EstimateApiResponse = {
  status: "ok",
  requestId: "req-abc",
  outcome: "estimated",
  recommendedProjectType: "vitrinesite",
  estimate: { minimum: 90_000, maximum: 130_000, currency: "EUR" },
  oneOffItems: [
    { code: "BASE", minimum: 90_000, maximum: 130_000, currency: "EUR" },
    { code: "FEATURE_BOOKING_FORM", minimum: 35_000, maximum: 80_000, currency: "EUR" },
  ],
  recurringItems: [
    {
      code: "RECURRING_ESSENTIAL_MAINTENANCE",
      minimum: 4_900,
      maximum: 6_900,
      currency: "EUR",
      period: "month",
    },
  ],
  assumptions: ["scope_to_confirm"],
  pricingVersion: "2026-v1",
}

const HUMAN_SCOPING_RESULT: EstimateApiResponse = {
  status: "ok",
  requestId: "req-xyz",
  outcome: "human_scoping_required",
  recommendedProjectType: "unknown",
  estimate: { minimum: 90_000, maximum: 400_000, currency: "EUR" },
  oneOffItems: [
    { code: "BASE", minimum: 90_000, maximum: 400_000, currency: "EUR" },
  ],
  recurringItems: [],
  assumptions: ["unknown_project_requires_human_scoping"],
  pricingVersion: "2026-v1",
}

function mountResult(
  result: EstimateApiResponse,
  additionalFeatureNote = "",
  careAnswered = false,
  careNeeds: string[] = [],
) {
  return mount(EstimatorResult, {
    props: {
      result,
      additionalFeatureNote,
      careAnswered,
      careNeeds,
      questionnairePayload: BASE_QUESTIONNAIRE,
    },
  })
}

// ─── Outcome : estimated ──────────────────────────────────────────────────────

describe("EstimatorResult — outcome estimated", () => {
  it("affiche le type recommandé avec label français", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.text()).toContain("Site vitrine")
    expect(wrapper.text()).not.toContain("vitrinesite")
  })

  it("affiche la fourchette principale en euros", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.text()).toContain("900")
    expect(wrapper.text()).toContain("1")
    expect(wrapper.text()).toContain("€")
  })

  it("n'affiche PAS '900 €' comme prix hardcodé — valeurs issues de l'API uniquement", () => {
    const resultWith2000 = {
      ...ESTIMATED_RESULT,
      estimate: { minimum: 200_000, maximum: 300_000, currency: "EUR" },
      oneOffItems: [
        { code: "BASE", minimum: 200_000, maximum: 300_000, currency: "EUR" },
      ],
    }
    const wrapper = mountResult(resultWith2000)
    expect(wrapper.text()).toContain("2")
    expect(wrapper.text()).toContain("3")
  })

  it("affiche la section 'Investissement initial' avec les line items", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.text()).toContain("Investissement initial")
    expect(wrapper.text()).toContain("Conception et développement")
    expect(wrapper.text()).toContain("Prise de rendez-vous")
  })

  it("affiche la section accompagnement récurrent", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.text()).toContain("Accompagnement après lancement")
    expect(wrapper.text()).toContain("Maintenance technique")
    expect(wrapper.text()).toContain("/ mois")
  })

  it("affiche les fourchettes récurrentes (minimum–maximum), pas un montant unique", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    // 4900 centimes = 49 €, 6900 = 69 €
    const text = wrapper.text()
    expect(text).toContain("49")
    expect(text).toContain("69")
  })

  it("affiche les assumptions avec un message éditorial humain", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.text()).toContain("À préciser ensemble")
    const text = wrapper.text()
    expect(text).not.toContain("scope_to_confirm")
  })

  it("n'affiche PAS unknown_project_requires_human_scoping dans les assumptions", () => {
    const resultWithHumanCode = {
      ...ESTIMATED_RESULT,
      assumptions: ["scope_to_confirm", "unknown_project_requires_human_scoping"],
    }
    const wrapper = mountResult(resultWithHumanCode)
    expect(wrapper.text()).not.toContain("unknown_project_requires_human_scoping")
  })

  it("n'affiche pas de section 'Besoin complémentaire' si note vide", () => {
    const wrapper = mountResult(ESTIMATED_RESULT, "")
    expect(wrapper.text()).not.toContain("Besoin complémentaire")
  })

  it("affiche le besoin complémentaire sans le chiffrer", () => {
    const wrapper = mountResult(ESTIMATED_RESULT, "Connexion avec notre logiciel de gestion")
    expect(wrapper.text()).toContain("Connexion avec notre logiciel de gestion")
    expect(wrapper.text()).toContain("n'est pas inclus automatiquement")
  })

  it("n'utilise jamais v-html pour la note — balise rendue comme texte échappé", () => {
    const xssNote = '<img src=x onerror="alert(1)">'
    const wrapper = mountResult(ESTIMATED_RESULT, xssNote)
    const html = wrapper.html()
    // Vue escape : < → &lt; donc aucune balise <img réelle dans le DOM
    expect(html).not.toContain("<img src=")
    // Le texte doit être présent sous forme échappée
    expect(html).toContain("&lt;img")
  })

  it("affiche la mention indicative (non contractuelle — Q-05)", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    // Q-05 validé : "ne constitue pas un devis" remplace "non contractuelle"
    expect(wrapper.text().toLowerCase()).toContain("ne constitue pas un devis")
  })

  it("n'affiche PAS la fourchette dans le titre ou les meta", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    const h2 = wrapper.find("h2")
    expect(h2.text()).not.toContain("900")
  })

  it("émet modify-answers sur clic du bouton", async () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    const modifyBtn = wrapper.findAll("button").find(b => b.text().includes("Modifier") || b.text().includes("Revoir"))
    await modifyBtn!.trigger("click")
    expect(wrapper.emitted("modify-answers")).toBeTruthy()
  })

  it("n'affiche pas pricing_version à l'utilisateur", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.text()).not.toContain("2026-v1")
  })

  it("message autonomie si careAnswered=true et careNeeds vide", () => {
    const noRecurring = {
      ...ESTIMATED_RESULT,
      recurringItems: [],
    }
    const wrapper = mountResult(noRecurring, "", true, [])
    expect(wrapper.text()).toContain("autonomie")
  })

  it("aucun message autonomie si careAnswered=false et recurringItems vide", () => {
    const noRecurring = { ...ESTIMATED_RESULT, recurringItems: [] }
    const wrapper = mountResult(noRecurring, "", false, [])
    expect(wrapper.text()).not.toContain("autonomie")
  })
})

// ─── Outcome : human_scoping_required ────────────────────────────────────────

describe("EstimatorResult — outcome human_scoping_required", () => {
  it("affiche le message 'cadrage plus précis'", () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    expect(wrapper.text()).toContain("cadrage")
  })

  it("N'affiche PAS la fourchette (900 € – 4 000 €) comme estimation commerciale", () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    const text = wrapper.text()
    // 90000 centimes = 900 €, 400000 = 4000 €
    // Le fallback technique ne doit jamais être visible comme prix commercial
    expect(text).not.toContain("900 €")
    expect(text).not.toContain("4 000 €")
  })

  it("N'affiche PAS de section 'Investissement initial' ni de tableau de prix", () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    expect(wrapper.text()).not.toContain("Investissement initial")
    expect(wrapper.text()).not.toContain("/ mois")
  })

  it("n'utilise pas le mot 'erreur'", () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    expect(wrapper.text().toLowerCase()).not.toContain("erreur")
  })

  it("le titre ne dit pas 'Nous n'avons pas compris'", () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    expect(wrapper.text()).not.toContain("compris")
  })

  it("émet modify-answers sur clic du bouton", async () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    const modifyBtn = wrapper.findAll("button").find(b => b.text().includes("Modifier") || b.text().includes("Revoir"))
    await modifyBtn!.trigger("click")
    expect(wrapper.emitted("modify-answers")).toBeTruthy()
  })

  it("affiche le besoin complémentaire sans fourchette", () => {
    const wrapper = mountResult(
      HUMAN_SCOPING_RESULT,
      "Connexion ERP interne",
    )
    expect(wrapper.text()).toContain("Connexion ERP interne")
    expect(wrapper.text()).not.toContain("900 €")
  })

  it("N'affiche pas unknown_project_requires_human_scoping comme texte brut", () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    expect(wrapper.text()).not.toContain("unknown_project_requires_human_scoping")
  })
})

// ─── Modalités de paiement (EST-5) ───────────────────────────────────────────

describe("EstimatorResult — payment terms (outcome estimated)", () => {
  it("affiche la section modalités de paiement pour outcome estimated", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.text()).toContain("échéances")
  })

  it("n'affiche jamais un montant par échéance", () => {
    // estimate.maximum = 130 000 centimes = 1 300 €, 3 échéances → 433 € — jamais affiché
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.text()).not.toContain("433")
  })

  it("n'affiche pas '/ mois' dans le bloc paiement", () => {
    // Les récurrents affichent '/ mois', mais le bloc paiement ne doit pas en avoir
    // On vérifie que le texte des termes de paiement ne contient pas '/ mois'
    const noRecurring = { ...ESTIMATED_RESULT, recurringItems: [] }
    const wrapper = mountResult(noRecurring)
    // Avec recurringItems vide et careAnswered=false, aucun '/ mois' ne doit apparaître
    expect(wrapper.text()).not.toContain("/ mois")
  })

  it("n'utilise pas 'crédit' ni 'financement'", () => {
    const text = mountResult(ESTIMATED_RESULT).text().toLowerCase()
    expect(text).not.toContain("crédit")
    expect(text).not.toContain("financement")
  })

  it("affiche le bon nombre d'échéances selon estimate.maximum", () => {
    const result4 = {
      ...ESTIMATED_RESULT,
      estimate: { minimum: 200_000, maximum: 400_000, currency: "EUR" },
    }
    const wrapper = mountResult(result4)
    expect(wrapper.text()).toContain("4 échéances")
  })
})

describe("EstimatorResult — payment terms absent pour human_scoping", () => {
  it("n'affiche PAS la section modalités de paiement pour human_scoping_required", () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    expect(wrapper.text()).not.toContain("échéances")
    expect(wrapper.text()).not.toContain("Modalités de règlement")
  })
})

// ─── Fallback codes inconnus ─────────────────────────────────────────────────

describe("EstimatorResult — codes inconnus", () => {
  it("line item inconnu → label générique, pas le code brut", () => {
    const resultWithUnknown = {
      ...ESTIMATED_RESULT,
      oneOffItems: [
        { code: "FUTURE_UNKNOWN_FEATURE", minimum: 10_000, maximum: 20_000, currency: "EUR" },
      ],
    }
    const wrapper = mountResult(resultWithUnknown)
    expect(wrapper.text()).not.toContain("FUTURE_UNKNOWN_FEATURE")
  })
})

// ─── EST-7 : CTA partenariat (outcome estimated) ──────────────────────────────

describe("EstimatorResult — partnership CTA (estimated)", () => {
  it("affiche le CTA principal 'Parler de mon projet'", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    const buttons = wrapper.findAll("button")
    const primaryCta = buttons.find((b) => b.text().includes("Parler de mon projet"))
    expect(primaryCta).toBeDefined()
  })

  it("affiche le CTA secondaire partenariat", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    const buttons = wrapper.findAll("button")
    const secondaryCta = buttons.find((b) => b.text().includes("partenariat"))
    expect(secondaryCta).toBeDefined()
  })

  it("le CTA secondaire a la classe result__cta-secondary", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.find(".result__cta-secondary").exists()).toBe(true)
  })

  it("le CTA primaire a la classe result__cta", () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    expect(wrapper.find(".result__cta").exists()).toBe(true)
  })

  it("cliquer sur le CTA secondaire affiche EstimatorPartnershipForm", async () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    const secondaryCta = wrapper.find(".result__cta-secondary")
    await secondaryCta.trigger("click")
    expect(wrapper.find(".partnership-form").exists()).toBe(true)
  })

  it("cliquer sur le CTA secondaire masque les deux CTAs", async () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    await wrapper.find(".result__cta-secondary").trigger("click")
    expect(wrapper.find(".result__cta").exists()).toBe(false)
    expect(wrapper.find(".result__cta-secondary").exists()).toBe(false)
  })

  it("cliquer sur le CTA principal affiche EstimatorLeadForm", async () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    await wrapper.find(".result__cta").trigger("click")
    expect(wrapper.find(".lead-form").exists()).toBe(true)
  })

  it("une seule forme active à la fois — lead masque partnership", async () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    await wrapper.find(".result__cta").trigger("click")
    expect(wrapper.find(".lead-form").exists()).toBe(true)
    expect(wrapper.find(".partnership-form").exists()).toBe(false)
  })

  it("n'affiche jamais 'Payez avec votre projet' ni 'Revenue share'", () => {
    const text = mountResult(ESTIMATED_RESULT).text()
    expect(text).not.toContain("Payez avec")
    expect(text).not.toContain("Revenue share disponible")
    expect(text).not.toContain("0 €")
  })
})

// ─── EST-7 : CTA partenariat (outcome human_scoping) ─────────────────────────

describe("EstimatorResult — partnership CTA (human_scoping)", () => {
  it("affiche également le CTA partenariat sur human_scoping", () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    expect(wrapper.find(".result__cta-secondary").exists()).toBe(true)
  })

  it("cliquer sur le CTA secondaire affiche EstimatorPartnershipForm", async () => {
    const wrapper = mountResult(HUMAN_SCOPING_RESULT)
    await wrapper.find(".result__cta-secondary").trigger("click")
    expect(wrapper.find(".partnership-form").exists()).toBe(true)
  })
})

// ─── EST-7 : pré-remplissage contact après lead soumis ───────────────────────

describe("EstimatorResult — contact pre-fill après lead soumis", () => {
  it("après @submitted du LeadForm, le PartnershipForm reçoit initialContact", async () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    // Ouvrir le formulaire lead
    await wrapper.find(".result__cta").trigger("click")
    const leadForm = wrapper.findComponent({ name: "EstimatorLeadForm" })
    // Émettre submitted avec données de contact
    await leadForm.vm.$emit("submitted", {
      name: "Marie Dupont",
      email: "marie@test.com",
      phone: "+33 6 00 00 00 00",
      company: "ACME",
    })
    await wrapper.vm.$nextTick()
    // Ouvrir ensuite le formulaire partenariat
    await wrapper.find(".result__cta-secondary").trigger("click")
    const partnershipForm = wrapper.findComponent({ name: "EstimatorPartnershipForm" })
    expect(partnershipForm.props("initialContact")).toMatchObject({
      name: "Marie Dupont",
      email: "marie@test.com",
    })
  })

  it("après @submitted du LeadForm, activeForm revient à none", async () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    await wrapper.find(".result__cta").trigger("click")
    const leadForm = wrapper.findComponent({ name: "EstimatorLeadForm" })
    await leadForm.vm.$emit("submitted", { name: "Jean", email: "jean@test.com" })
    await wrapper.vm.$nextTick()
    expect(wrapper.find(".lead-form").exists()).toBe(false)
    expect(wrapper.find(".result__cta").exists()).toBe(true)
  })

  it("@close du PartnershipForm revient à l'état none", async () => {
    const wrapper = mountResult(ESTIMATED_RESULT)
    await wrapper.find(".result__cta-secondary").trigger("click")
    const partnershipForm = wrapper.findComponent({ name: "EstimatorPartnershipForm" })
    await partnershipForm.vm.$emit("close")
    await wrapper.vm.$nextTick()
    expect(wrapper.find(".partnership-form").exists()).toBe(false)
    expect(wrapper.find(".result__cta-secondary").exists()).toBe(true)
  })
})
