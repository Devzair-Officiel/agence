import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorFeaturesStep from "~/components/estimator/EstimatorFeaturesStep.vue"
import { getFeaturesForType } from "~/config/estimator-features"

describe("EstimatorFeaturesStep", () => {
  it("renders a fieldset with a legend", () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
    })
    expect(wrapper.find("fieldset").exists()).toBe(true)
    expect(wrapper.find("legend").exists()).toBe(true)
  })

  it("renders only features relevant to the project type", () => {
    const vitrineFeaturesCount = getFeaturesForType("vitrinesite").length
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
    })
    const inputs = wrapper.findAll("input[type='checkbox']")
    expect(inputs).toHaveLength(vitrineFeaturesCount)
  })

  it("renders all 15 features for refonte type", () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], additionalFeatureNote: "", projectType: "refonte" },
    })
    const inputs = wrapper.findAll("input[type='checkbox']")
    expect(inputs).toHaveLength(15)
  })

  it("emits update:modelValue adding a feature on toggle", async () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
    })
    await wrapper.find("input[value='contact_form']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).toContain("contact_form")
  })

  it("emits update:modelValue removing a feature when already selected", async () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: ["contact_form", "booking_form"], additionalFeatureNote: "", projectType: "vitrinesite" },
    })
    await wrapper.find("input[value='contact_form']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).not.toContain("contact_form")
    expect(emitted).toContain("booking_form")
  })

  it("shows hint for optional selection", () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
    })
    expect(wrapper.find(".features-step__hint").exists()).toBe(true)
  })

  it("legend has tabindex=-1 for focus management", () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
    })
    expect(wrapper.find("legend").attributes("tabindex")).toBe("-1")
  })

  describe("additionalFeatureNote textarea", () => {
    it("renders a textarea with id=additional-feature-note", () => {
      const wrapper = mount(EstimatorFeaturesStep, {
        props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
      })
      expect(wrapper.find("textarea#additional-feature-note").exists()).toBe(true)
    })

    it("textarea has maxlength=400", () => {
      const wrapper = mount(EstimatorFeaturesStep, {
        props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
      })
      expect(wrapper.find("textarea").attributes("maxlength")).toBe("400")
    })

    it("shows character counter", () => {
      const wrapper = mount(EstimatorFeaturesStep, {
        props: { modelValue: [], additionalFeatureNote: "Hello", projectType: "vitrinesite" },
      })
      expect(wrapper.find(".features-step__counter").text()).toContain("5 / 400")
    })

    it("shows disclaimer text", () => {
      const wrapper = mount(EstimatorFeaturesStep, {
        props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
      })
      expect(wrapper.find(".features-step__disclaimer").exists()).toBe(true)
      expect(wrapper.find(".features-step__disclaimer").text()).toContain(
        "Ce besoin sera étudié avec vous",
      )
    })

    it("emits update:additionalFeatureNote on input", async () => {
      const wrapper = mount(EstimatorFeaturesStep, {
        props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
      })
      const textarea = wrapper.find("textarea")
      await textarea.setValue("Chat en temps réel")
      const emitted = wrapper.emitted("update:additionalFeatureNote")
      expect(emitted).toBeTruthy()
      expect(emitted?.[0]?.[0]).toBe("Chat en temps réel")
    })

    it("label 'Une autre fonctionnalité à prévoir ?' is associated to textarea", () => {
      const wrapper = mount(EstimatorFeaturesStep, {
        props: { modelValue: [], additionalFeatureNote: "", projectType: "vitrinesite" },
      })
      const label = wrapper.find("label.features-step__extra-label")
      expect(label.attributes("for")).toBe("additional-feature-note")
    })
  })
})
