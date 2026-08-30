import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorCareStep from "~/components/estimator/EstimatorCareStep.vue"
import { ESTIMATOR_CARE_OPTIONS } from "~/config/estimator-care-options"

describe("EstimatorCareStep", () => {
  it("renders a fieldset with a legend", () => {
    const wrapper = mount(EstimatorCareStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("fieldset").exists()).toBe(true)
    expect(wrapper.find("legend").exists()).toBe(true)
  })

  it("renders all care options as checkboxes", () => {
    const wrapper = mount(EstimatorCareStep, {
      props: { modelValue: [] },
    })
    const mainInputs = wrapper.findAll("input[name='care-needs']")
    expect(mainInputs).toHaveLength(ESTIMATOR_CARE_OPTIONS.length)
  })

  it("renders autonomy option", () => {
    const wrapper = mount(EstimatorCareStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("input[name='care-autonomy']").exists()).toBe(true)
  })

  it("autonomy card appears selected when modelValue is empty", () => {
    const wrapper = mount(EstimatorCareStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find(".care-step__autonomy-card--selected").exists()).toBe(true)
  })

  it("autonomy card not selected when care needs are selected", () => {
    const wrapper = mount(EstimatorCareStep, {
      props: { modelValue: ["maintenance"] },
    })
    expect(wrapper.find(".care-step__autonomy-card--selected").exists()).toBe(false)
  })

  it("emits update:modelValue adding a care need on toggle", async () => {
    const wrapper = mount(EstimatorCareStep, {
      props: { modelValue: [] },
    })
    await wrapper.find("input[value='maintenance']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).toContain("maintenance")
  })

  it("emits empty array when autonomy is clicked", async () => {
    const wrapper = mount(EstimatorCareStep, {
      props: { modelValue: ["maintenance", "support"] },
    })
    await wrapper.find("input[name='care-autonomy']").trigger("change")
    expect(wrapper.emitted("update:modelValue")?.[0]).toEqual([[]])
  })

  it("legend has tabindex=-1 for focus management", () => {
    const wrapper = mount(EstimatorCareStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("legend").attributes("tabindex")).toBe("-1")
  })
})
