import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorContentStep from "~/components/estimator/EstimatorContentStep.vue"

describe("EstimatorContentStep", () => {
  it("renders identity, content and photography sections", () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: [] },
    })
    const radios = wrapper.findAll("input[type='radio']")
    expect(radios.length).toBeGreaterThan(0)
    const checkboxes = wrapper.findAll("input[type='checkbox']")
    expect(checkboxes.length).toBeGreaterThan(0)
  })

  it("renders identity radio options", () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("input[value='ready'][name='identity-status']").exists()).toBe(true)
    expect(wrapper.find("input[value='partial'][name='identity-status']").exists()).toBe(true)
    expect(wrapper.find("input[value='needed'][name='identity-status']").exists()).toBe(true)
  })

  it("renders content text radio options", () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("input[value='ready'][name='content-status']").exists()).toBe(true)
    expect(wrapper.find("input[value='with_help'][name='content-status']").exists()).toBe(true)
    expect(wrapper.find("input[value='to_write'][name='content-status']").exists()).toBe(true)
  })

  it("initializes identity as ready when modelValue is empty", () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: [] },
    })
    const readyInput = wrapper.find("input[value='ready'][name='identity-status']")
    expect((readyInput.element as HTMLInputElement).checked).toBe(true)
  })

  it("initializes from modelValue: visual_identity_needed", () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: ["visual_identity_needed"] },
    })
    const neededInput = wrapper.find("input[value='needed'][name='identity-status']")
    expect((neededInput.element as HTMLInputElement).checked).toBe(true)
  })

  it("initializes from modelValue: photography_needed", () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: ["photography_needed"] },
    })
    const photoCheckbox = wrapper.find("input[name='photography']")
    expect((photoCheckbox.element as HTMLInputElement).checked).toBe(true)
  })

  it("emits visual_identity_needed when identity set to needed", async () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: [] },
    })
    await wrapper.find("input[value='needed'][name='identity-status']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).toContain("visual_identity_needed")
  })

  it("emits visual_identity_partial when identity set to partial", async () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: [] },
    })
    await wrapper.find("input[value='partial'][name='identity-status']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).toContain("visual_identity_partial")
  })

  it("does not emit visual identity code when identity is ready", async () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: ["visual_identity_needed"] },
    })
    await wrapper.find("input[value='ready'][name='identity-status']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).not.toContain("visual_identity_needed")
    expect(emitted).not.toContain("visual_identity_partial")
  })

  it("emits photography_needed when photo checkbox toggled", async () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: [] },
    })
    await wrapper.find("input[name='photography']").trigger("change")
    const emitted = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(emitted).toContain("photography_needed")
  })

  it("heading has tabindex=-1 for focus management", () => {
    const wrapper = mount(EstimatorContentStep, {
      props: { modelValue: [] },
    })
    expect(wrapper.find("h2[tabindex='-1']").exists()).toBe(true)
  })
})
