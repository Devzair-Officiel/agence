import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorSituationStep from "~/components/estimator/EstimatorSituationStep.vue"
import { ESTIMATOR_SITUATION_OPTIONS } from "~/config/estimator-situation-options"

describe("EstimatorSituationStep", () => {
  it("renders a fieldset with a legend", () => {
    const wrapper = mount(EstimatorSituationStep, {
      props: { modelValue: null },
    })
    expect(wrapper.find("fieldset").exists()).toBe(true)
    expect(wrapper.find("legend").exists()).toBe(true)
  })

  it("renders all situation options", () => {
    const wrapper = mount(EstimatorSituationStep, {
      props: { modelValue: null },
    })
    const inputs = wrapper.findAll("input[type='radio']")
    expect(inputs).toHaveLength(ESTIMATOR_SITUATION_OPTIONS.length)
  })

  it("emits update:modelValue with correct code on selection", async () => {
    const wrapper = mount(EstimatorSituationStep, {
      props: { modelValue: null },
    })
    const noneInput = wrapper.find("input[value='none']")
    await noneInput.trigger("change")
    expect(wrapper.emitted("update:modelValue")?.[0]).toEqual(["none"])
  })

  it("marks selected card with --selected modifier", () => {
    const wrapper = mount(EstimatorSituationStep, {
      props: { modelValue: "has_website" },
    })
    const selected = wrapper.find(".situation-card--selected")
    expect(selected.exists()).toBe(true)
    expect(selected.find("input").attributes("value")).toBe("has_website")
  })

  it("no card is selected when modelValue is null", () => {
    const wrapper = mount(EstimatorSituationStep, {
      props: { modelValue: null },
    })
    expect(wrapper.find(".situation-card--selected").exists()).toBe(false)
  })

  it("legend has tabindex=-1 for focus management", () => {
    const wrapper = mount(EstimatorSituationStep, {
      props: { modelValue: null },
    })
    expect(wrapper.find("legend").attributes("tabindex")).toBe("-1")
  })

  it("unknown option exists", () => {
    const wrapper = mount(EstimatorSituationStep, {
      props: { modelValue: null },
    })
    expect(wrapper.find("input[value='unknown']").exists()).toBe(true)
  })
})
