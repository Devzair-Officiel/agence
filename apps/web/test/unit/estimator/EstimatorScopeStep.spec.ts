import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorScopeStep from "~/components/estimator/EstimatorScopeStep.vue"
import { getScaleConfig } from "~/config/estimator-scale-options"

describe("EstimatorScopeStep", () => {
  it("renders a fieldset with a legend", () => {
    const wrapper = mount(EstimatorScopeStep, {
      props: { modelValue: null, projectType: "vitrinesite" },
    })
    expect(wrapper.find("fieldset").exists()).toBe(true)
    expect(wrapper.find("legend").exists()).toBe(true)
  })

  it("shows type-specific legend for vitrinesite", () => {
    const wrapper = mount(EstimatorScopeStep, {
      props: { modelValue: null, projectType: "vitrinesite" },
    })
    const config = getScaleConfig("vitrinesite")
    expect(wrapper.find("legend").text()).toContain(config.legend)
  })

  it("shows type-specific legend for ecommerce", () => {
    const wrapper = mount(EstimatorScopeStep, {
      props: { modelValue: null, projectType: "ecommerce" },
    })
    const config = getScaleConfig("ecommerce")
    expect(wrapper.find("legend").text()).toContain(config.legend)
  })

  it("shows type-specific legend for businessapp", () => {
    const wrapper = mount(EstimatorScopeStep, {
      props: { modelValue: null, projectType: "businessapp" },
    })
    const config = getScaleConfig("businessapp")
    expect(wrapper.find("legend").text()).toContain(config.legend)
  })

  it("renders 4 scale options", () => {
    const wrapper = mount(EstimatorScopeStep, {
      props: { modelValue: null, projectType: "vitrinesite" },
    })
    expect(wrapper.findAll("input[type='radio']")).toHaveLength(4)
  })

  it("emits update:modelValue with correct code on selection", async () => {
    const wrapper = mount(EstimatorScopeStep, {
      props: { modelValue: null, projectType: "vitrinesite" },
    })
    await wrapper.find("input[value='small']").trigger("change")
    expect(wrapper.emitted("update:modelValue")?.[0]).toEqual(["small"])
  })

  it("marks selected scale card with --selected modifier", () => {
    const wrapper = mount(EstimatorScopeStep, {
      props: { modelValue: "medium", projectType: "ecommerce" },
    })
    const selected = wrapper.find(".scope-card--selected")
    expect(selected.exists()).toBe(true)
    expect(selected.find("input").attributes("value")).toBe("medium")
  })

  it("legend has tabindex=-1 for focus management", () => {
    const wrapper = mount(EstimatorScopeStep, {
      props: { modelValue: null, projectType: "vitrinesite" },
    })
    expect(wrapper.find("legend").attributes("tabindex")).toBe("-1")
  })
})
