import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorFeaturesStep from "~/components/estimator/EstimatorFeaturesStep.vue"
import { getFeaturesForType } from "~/config/estimator-features"

describe("EstimatorFeaturesStep", () => {
  it("renders a fieldset with a legend", () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], projectType: "vitrinesite" },
    })
    expect(wrapper.find("fieldset").exists()).toBe(true)
    expect(wrapper.find("legend").exists()).toBe(true)
  })

  it("renders only features relevant to the project type", () => {
    const vitrineFeaturesCount = getFeaturesForType("vitrinesite").length
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], projectType: "vitrinesite" },
    })
    const inputs = wrapper.findAll("input[type='checkbox']")
    expect(inputs).toHaveLength(vitrineFeaturesCount)
  })

  it("renders all 15 features for refonte type", () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], projectType: "refonte" },
    })
    const inputs = wrapper.findAll("input[type='checkbox']")
    expect(inputs).toHaveLength(15)
  })

  it("emits update:modelValue adding a feature on toggle", async () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], projectType: "vitrinesite" },
    })
    await wrapper.find("input[value='contact_form']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).toContain("contact_form")
  })

  it("emits update:modelValue removing a feature when already selected", async () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: ["contact_form", "booking_form"], projectType: "vitrinesite" },
    })
    await wrapper.find("input[value='contact_form']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).not.toContain("contact_form")
    expect(emitted).toContain("booking_form")
  })

  it("shows hint for optional selection", () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], projectType: "vitrinesite" },
    })
    expect(wrapper.find(".features-step__hint").exists()).toBe(true)
  })

  it("legend has tabindex=-1 for focus management", () => {
    const wrapper = mount(EstimatorFeaturesStep, {
      props: { modelValue: [], projectType: "vitrinesite" },
    })
    expect(wrapper.find("legend").attributes("tabindex")).toBe("-1")
  })
})
