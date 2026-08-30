import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorVisibilityStep from "~/components/estimator/EstimatorVisibilityStep.vue"
import { ESTIMATOR_VISIBILITY_OPTIONS } from "~/config/estimator-visibility-options"

describe("EstimatorVisibilityStep", () => {
  it("renders a fieldset with a legend", () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("fieldset").exists()).toBe(true)
    expect(wrapper.find("legend").exists()).toBe(true)
  })

  it("renders all visibility options as checkboxes", () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: [] },
    })
    const inputs = wrapper.findAll("input[type='checkbox']")
    expect(inputs).toHaveLength(ESTIMATOR_VISIBILITY_OPTIONS.length)
  })

  it("emits update:modelValue adding a code on toggle", async () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: [] },
    })
    await wrapper.find("input[value='seo_basic']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).toContain("seo_basic")
  })

  it("emits update:modelValue removing a code when already selected", async () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: ["seo_basic", "local_visibility"] },
    })
    await wrapper.find("input[value='seo_basic']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).not.toContain("seo_basic")
    expect(emitted).toContain("local_visibility")
  })

  it("legend has tabindex=-1 for focus management", () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("legend").attributes("tabindex")).toBe("-1")
  })
})
