import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorProjectSummary from "~/components/estimator/EstimatorProjectSummary.vue"

const baseProps = {
  currentStep: 1,
  maxVisitedStep: 1,
  path: "standard" as const,
  projectType: null,
  objectives: [],
  currentSituation: null,
  scale: null,
  features: [],
  contentNeeds: [],
  visibilityNeeds: [],
  careNeeds: [],
  careAnswered: false,
  additionalFeatureNote: "",
}

describe("EstimatorProjectSummary", () => {
  describe("structure", () => {
    it("renders an aside with correct aria-label", () => {
      const wrapper = mount(EstimatorProjectSummary, { props: baseProps })
      const aside = wrapper.find("aside")
      expect(aside.exists()).toBe(true)
      expect(aside.attributes("aria-label")).toBe("Récapitulatif de votre projet")
    })

    it("renders a toggle button with aria-expanded", () => {
      const wrapper = mount(EstimatorProjectSummary, { props: baseProps })
      const toggle = wrapper.find("button.summary__toggle")
      expect(toggle.exists()).toBe(true)
      expect(toggle.attributes("aria-expanded")).toBe("false")
    })

    it("toggle button aria-expanded becomes true after click", async () => {
      const wrapper = mount(EstimatorProjectSummary, { props: baseProps })
      const toggle = wrapper.find("button.summary__toggle")
      await toggle.trigger("click")
      expect(toggle.attributes("aria-expanded")).toBe("true")
    })

    it("summary body has id=summary-body matching toggle aria-controls", () => {
      const wrapper = mount(EstimatorProjectSummary, { props: baseProps })
      expect(wrapper.find("#summary-body").exists()).toBe(true)
      expect(wrapper.find("button.summary__toggle").attributes("aria-controls")).toBe("summary-body")
    })
  })

  describe("sections — standard path", () => {
    it("shows only step 1 when maxVisitedStep=1", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: { ...baseProps, projectType: "vitrinesite", maxVisitedStep: 1 },
      })
      const sections = wrapper.findAll(".summary__section")
      expect(sections).toHaveLength(1)
    })

    it("shows project type label in step 1 section", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: { ...baseProps, projectType: "vitrinesite", maxVisitedStep: 1 },
      })
      expect(wrapper.text()).toContain("Site vitrine / institutionnel")
    })

    it("shows 2 sections when maxVisitedStep=2", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 2,
          currentStep: 2,
        },
      })
      const sections = wrapper.findAll(".summary__section")
      expect(sections).toHaveLength(2)
    })

    it("shows situation label when set", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 2,
          currentSituation: "none",
        },
      })
      expect(wrapper.text()).toContain("Non, c'est une création")
    })

    it("shows scale label when set", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 3,
          scale: "small",
        },
      })
      expect(wrapper.text()).toContain("1 à 5 pages")
    })

    it("shows feature labels in step 4", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 4,
          features: ["contact_form", "booking_form"],
        },
      })
      expect(wrapper.text()).toContain("Formulaire de contact")
      expect(wrapper.text()).toContain("Prise de rendez-vous")
    })

    it("shows 'Aucune sélection' for empty optional features", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 4,
          currentStep: 5,
          features: [],
        },
      })
      expect(wrapper.text()).toContain("Aucune sélection")
    })

    it("shows 7 sections on completed standard path", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 7,
          currentStep: 7,
          currentSituation: "none",
          scale: "small",
          careAnswered: true,
          careNeeds: ["maintenance"],
        },
      })
      const sections = wrapper.findAll(".summary__section")
      expect(sections).toHaveLength(7)
    })
  })

  describe("sections — unknown path", () => {
    it("shows 6 sections on completed unknown path", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          path: "unknown",
          projectType: "unknown",
          maxVisitedStep: 6,
          currentStep: 6,
          objectives: ["present_business"],
          currentSituation: "none",
          careAnswered: true,
          careNeeds: [],
        },
      })
      const sections = wrapper.findAll(".summary__section")
      expect(sections).toHaveLength(6)
    })

    it("shows 'Objectifs' section for unknown path", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          path: "unknown",
          projectType: "unknown",
          maxVisitedStep: 2,
          currentStep: 2,
          objectives: ["sell_online"],
        },
      })
      expect(wrapper.text()).toContain("Objectifs")
      expect(wrapper.text()).toContain("Vendre des produits ou services en ligne")
    })

    it("does not show 'Périmètre' or 'Fonctionnalités' sections for unknown path", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          path: "unknown",
          projectType: "unknown",
          maxVisitedStep: 6,
          currentStep: 6,
        },
      })
      expect(wrapper.text()).not.toContain("Périmètre")
      expect(wrapper.text()).not.toContain("Fonctionnalités")
    })
  })

  describe("current step highlight", () => {
    it("adds summary__section--current class to current step", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 2,
          currentStep: 2,
          currentSituation: null,
        },
      })
      const sections = wrapper.findAll(".summary__section")
      expect(sections[1].classes()).toContain("summary__section--current")
      expect(sections[0].classes()).not.toContain("summary__section--current")
    })
  })

  describe("Modifier button", () => {
    it("shows Modifier on non-current visited sections", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 2,
          currentStep: 2,
        },
      })
      const modifyButtons = wrapper.findAll("button.summary__modify")
      expect(modifyButtons).toHaveLength(1)
    })

    it("does not show Modifier on current step", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 1,
          currentStep: 1,
        },
      })
      const modifyButtons = wrapper.findAll("button.summary__modify")
      expect(modifyButtons).toHaveLength(0)
    })

    it("emits go-to-step with correct step number on Modifier click", async () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 2,
          currentStep: 2,
        },
      })
      await wrapper.find("button.summary__modify").trigger("click")
      const emitted = wrapper.emitted("go-to-step")
      expect(emitted).toBeTruthy()
      expect(emitted?.[0]?.[0]).toBe(1)
    })
  })

  describe("care autonomy display", () => {
    it("shows 'Autonomie (sans accompagnement)' when careAnswered and careNeeds empty", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 7,
          currentStep: 7,
          careAnswered: true,
          careNeeds: [],
        },
      })
      expect(wrapper.text()).toContain("Autonomie (sans accompagnement)")
    })

    it("shows 'À définir' when care step reached but not yet answered", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 7,
          currentStep: 7,
          careAnswered: false,
          careNeeds: [],
        },
      })
      expect(wrapper.text()).toContain("À définir")
    })

    it("shows care option labels when selected", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 7,
          careAnswered: true,
          careNeeds: ["maintenance", "support"],
        },
      })
      expect(wrapper.text()).toContain("Maintenance technique")
      expect(wrapper.text()).toContain("Support et assistance")
    })
  })

  describe("additionalFeatureNote", () => {
    it("shows truncated note under Fonctionnalités section", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 4,
          additionalFeatureNote: "Système de réservation en ligne",
        },
      })
      expect(wrapper.text()).toContain("Système de réservation en ligne")
    })

    it("truncates notes longer than 72 chars", () => {
      const longNote = "A".repeat(80)
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          projectType: "vitrinesite",
          maxVisitedStep: 4,
          additionalFeatureNote: longNote,
        },
      })
      expect(wrapper.text()).toContain("…")
      expect(wrapper.text()).not.toContain(longNote)
    })

    it("does not show extra-note on unknown path", () => {
      const wrapper = mount(EstimatorProjectSummary, {
        props: {
          ...baseProps,
          path: "unknown",
          projectType: "unknown",
          maxVisitedStep: 4,
          additionalFeatureNote: "Quelque chose",
        },
      })
      expect(wrapper.find(".summary__extra-note").exists()).toBe(false)
    })
  })
})
