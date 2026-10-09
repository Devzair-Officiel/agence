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

  it("renders only the optional (paid) visibility options as checkboxes", () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: [] },
    })
    const inputs = wrapper.findAll("input[type='checkbox']")
    const selectable = ESTIMATOR_VISIBILITY_OPTIONS.filter((o) => !o.included)
    expect(inputs.map((i) => i.attributes("value"))).toEqual(selectable.map((o) => o.value))
    expect(wrapper.find("input[value='seo_basic']").exists()).toBe(false)
  })

  it("shows the basic SEO as included at no extra cost, not as a choice", () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: [] },
    })
    const included = wrapper.findAll(".visibility-step__included")
    expect(included).toHaveLength(1)
    expect(included[0]!.text()).toContain("Référencement de base")
    expect(included[0]!.get(".visibility-step__included-badge").text()).toBe("Inclus")
    expect(included[0]!.text()).toContain("sans surcoût")
  })

  it("emits update:modelValue adding a code on toggle", async () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: [] },
    })
    await wrapper.find("input[value='local_visibility']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).toContain("local_visibility")
  })

  it("emits update:modelValue removing a code when already selected", async () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: ["seo_advanced", "local_visibility"] },
    })
    await wrapper.find("input[value='seo_advanced']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).not.toContain("seo_advanced")
    expect(emitted).toContain("local_visibility")
  })

  it("legend has tabindex=-1 for focus management", () => {
    const wrapper = mount(EstimatorVisibilityStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("legend").attributes("tabindex")).toBe("-1")
  })
})
