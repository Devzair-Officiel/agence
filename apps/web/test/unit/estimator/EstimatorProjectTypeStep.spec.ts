import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorProjectTypeStep from "~/components/estimator/EstimatorProjectTypeStep.vue"
import { ESTIMATOR_PROJECT_TYPES } from "~/config/estimator-project-types"

describe("EstimatorProjectTypeStep", () => {
  it("renders a fieldset with a legend", () => {
    const wrapper = mount(EstimatorProjectTypeStep, {
      props: { modelValue: null },
    })
    expect(wrapper.find("fieldset").exists()).toBe(true)
    expect(wrapper.find("legend").exists()).toBe(true)
  })

  it("renders all 6 project type options", () => {
    const wrapper = mount(EstimatorProjectTypeStep, {
      props: { modelValue: null },
    })
    const inputs = wrapper.findAll("input[type='radio']")
    expect(inputs).toHaveLength(ESTIMATOR_PROJECT_TYPES.length)
  })

  it("emits update:modelValue with correct code on radio change", async () => {
    const wrapper = mount(EstimatorProjectTypeStep, {
      props: { modelValue: null },
    })
    const vitrine = wrapper.find("input[value='vitrinesite']")
    await vitrine.trigger("change")
    expect(wrapper.emitted("update:modelValue")?.[0]).toEqual(["vitrinesite"])
  })

  it("marks selected card with --selected modifier", () => {
    const wrapper = mount(EstimatorProjectTypeStep, {
      props: { modelValue: "ecommerce" },
    })
    const selectedCard = wrapper.find(".project-type-card--selected")
    expect(selectedCard.exists()).toBe(true)
    const input = selectedCard.find("input[type='radio']")
    expect(input.attributes("value")).toBe("ecommerce")
  })

  it("no card is selected when modelValue is null", () => {
    const wrapper = mount(EstimatorProjectTypeStep, {
      props: { modelValue: null },
    })
    expect(wrapper.find(".project-type-card--selected").exists()).toBe(false)
  })

  it("unknown option exists", () => {
    const wrapper = mount(EstimatorProjectTypeStep, {
      props: { modelValue: null },
    })
    const unknownInput = wrapper.find("input[value='unknown']")
    expect(unknownInput.exists()).toBe(true)
  })
})
