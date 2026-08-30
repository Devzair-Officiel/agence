import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorProgress from "~/components/estimator/EstimatorProgress.vue"

describe("EstimatorProgress", () => {
  it("renders the progressbar role with correct aria attributes", () => {
    const wrapper = mount(EstimatorProgress, {
      props: { currentStep: 3, totalSteps: 7 },
    })
    const bar = wrapper.get("[role='progressbar']")
    expect(bar.attributes("aria-valuenow")).toBe("3")
    expect(bar.attributes("aria-valuemin")).toBe("1")
    expect(bar.attributes("aria-valuemax")).toBe("7")
  })

  it("displays the step label text", () => {
    const wrapper = mount(EstimatorProgress, {
      props: { currentStep: 1, totalSteps: 7 },
    })
    expect(wrapper.text()).toContain("1")
    expect(wrapper.text()).toContain("7")
  })

  it("fill width is 1/7 ≈ 14% at step 1", () => {
    const wrapper = mount(EstimatorProgress, {
      props: { currentStep: 1, totalSteps: 7 },
    })
    const fill = wrapper.get(".estimator-progress__fill")
    expect(fill.attributes("style")).toContain("14%")
  })

  it("fill width is 100% at final step", () => {
    const wrapper = mount(EstimatorProgress, {
      props: { currentStep: 7, totalSteps: 7 },
    })
    const fill = wrapper.get(".estimator-progress__fill")
    expect(fill.attributes("style")).toContain("100%")
  })

  it("label text is aria-hidden", () => {
    const wrapper = mount(EstimatorProgress, {
      props: { currentStep: 2, totalSteps: 7 },
    })
    const label = wrapper.get(".estimator-progress__label")
    expect(label.attributes("aria-hidden")).toBe("true")
  })
})
