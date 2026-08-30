import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorObjectivesStep from "~/components/estimator/EstimatorObjectivesStep.vue"
import { ESTIMATOR_OBJECTIVES } from "~/config/estimator-objectives"

describe("EstimatorObjectivesStep", () => {
  it("renders a fieldset with a legend", () => {
    const wrapper = mount(EstimatorObjectivesStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("fieldset").exists()).toBe(true)
    expect(wrapper.find("legend").exists()).toBe(true)
  })

  it("renders all objective options as checkboxes", () => {
    const wrapper = mount(EstimatorObjectivesStep, {
      props: { modelValue: [] },
    })
    const inputs = wrapper.findAll("input[type='checkbox']")
    expect(inputs).toHaveLength(ESTIMATOR_OBJECTIVES.length)
  })

  it("emits update:modelValue adding a code on toggle", async () => {
    const wrapper = mount(EstimatorObjectivesStep, {
      props: { modelValue: [] },
    })
    const sellOnline = wrapper.find("input[value='sell_online']")
    await sellOnline.trigger("change")
    expect(wrapper.emitted("update:modelValue")?.[0]).toEqual([["sell_online"]])
  })

  it("emits update:modelValue removing a code when already selected", async () => {
    const wrapper = mount(EstimatorObjectivesStep, {
      props: { modelValue: ["sell_online", "generate_leads"] },
    })
    const sellOnline = wrapper.find("input[value='sell_online']")
    await sellOnline.trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).not.toContain("sell_online")
    expect(emitted).toContain("generate_leads")
  })

  it("legend has tabindex=-1 for focus management", () => {
    const wrapper = mount(EstimatorObjectivesStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("legend").attributes("tabindex")).toBe("-1")
  })

  it("shows hint for multi-selection", () => {
    const wrapper = mount(EstimatorObjectivesStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find(".objectives-step__hint").exists()).toBe(true)
  })
})
