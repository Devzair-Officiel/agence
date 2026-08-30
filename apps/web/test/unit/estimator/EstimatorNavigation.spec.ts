import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorNavigation from "~/components/estimator/EstimatorNavigation.vue"

describe("EstimatorNavigation", () => {
  it("shows Continuer button always", () => {
    const wrapper = mount(EstimatorNavigation, {
      props: { currentStep: 1, canGoNext: false },
    })
    expect(wrapper.text()).toContain("Continuer")
  })

  it("Continuer is disabled when canGoNext is false", () => {
    const wrapper = mount(EstimatorNavigation, {
      props: { currentStep: 1, canGoNext: false },
    })
    const next = wrapper.get(".estimator-nav__next")
    expect(next.attributes("disabled")).toBeDefined()
  })

  it("does not show Retour at step 1", () => {
    const wrapper = mount(EstimatorNavigation, {
      props: { currentStep: 1, canGoNext: false },
    })
    expect(wrapper.text()).not.toContain("Retour")
  })

  it("shows Retour at step 2+", () => {
    const wrapper = mount(EstimatorNavigation, {
      props: { currentStep: 2, canGoNext: true },
    })
    expect(wrapper.text()).toContain("Retour")
  })

  it("Continuer activé : émet next au clic", async () => {
    const wrapper = mount(EstimatorNavigation, {
      props: { currentStep: 1, canGoNext: true },
    })
    await wrapper.get(".estimator-nav__next").trigger("click")
    expect(wrapper.emitted("next")).toHaveLength(1)
  })

  it("emits prev on Retour click", async () => {
    const wrapper = mount(EstimatorNavigation, {
      props: { currentStep: 2, canGoNext: true },
    })
    await wrapper.get(".estimator-nav__back").trigger("click")
    expect(wrapper.emitted("prev")).toHaveLength(1)
  })
})
