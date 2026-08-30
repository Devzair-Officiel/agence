import { describe, expect, it } from "vitest"

import { useEstimator } from "~/composables/useEstimator"

describe("useEstimator", () => {
  describe("initial state", () => {
    it("starts at step 1", () => {
      const { currentStep } = useEstimator()
      expect(currentStep.value).toBe(1)
    })

    it("projectType is null initially", () => {
      const { projectType } = useEstimator()
      expect(projectType.value).toBeNull()
    })

    it("canGoNext is false when no project type selected", () => {
      const { canGoNext } = useEstimator()
      expect(canGoNext.value).toBe(false)
    })
  })

  describe("setProjectType", () => {
    it("updates projectType", () => {
      const { projectType, setProjectType } = useEstimator()
      setProjectType("vitrinesite")
      expect(projectType.value).toBe("vitrinesite")
    })

    it("makes canGoNext true after selection", () => {
      const { canGoNext, setProjectType } = useEstimator()
      setProjectType("ecommerce")
      expect(canGoNext.value).toBe(true)
    })
  })

  describe("goNext", () => {
    it("does nothing when canGoNext is false", () => {
      const { currentStep, goNext } = useEstimator()
      goNext()
      expect(currentStep.value).toBe(1)
    })

    it("increments step when canGoNext is true", () => {
      const { currentStep, goNext, setProjectType } = useEstimator()
      setProjectType("refonte")
      goNext()
      expect(currentStep.value).toBe(2)
    })

    it("does not exceed maxStep", () => {
      const { currentStep, goNext, setProjectType } = useEstimator({
        maxStep: 1,
      })
      setProjectType("other")
      goNext()
      expect(currentStep.value).toBe(1)
    })

    it("respects custom maxStep", () => {
      const { currentStep, goNext, setProjectType } = useEstimator({
        maxStep: 3,
      })
      setProjectType("businessapp")
      goNext()
      expect(currentStep.value).toBe(2)
      goNext()
      expect(currentStep.value).toBe(3)
      goNext()
      expect(currentStep.value).toBe(3)
    })
  })

  describe("goPrev", () => {
    it("does nothing at step 1", () => {
      const { currentStep, goPrev } = useEstimator()
      goPrev()
      expect(currentStep.value).toBe(1)
    })

    it("decrements step from step 2", () => {
      const { currentStep, goNext, goPrev, setProjectType } = useEstimator()
      setProjectType("vitrinesite")
      goNext()
      expect(currentStep.value).toBe(2)
      goPrev()
      expect(currentStep.value).toBe(1)
    })
  })

  describe("EST-2 maxStep=1 guard", () => {
    it("Continuer reste sur étape 1 quand maxStep=1", () => {
      const { currentStep, goNext, setProjectType } = useEstimator({
        maxStep: 1,
      })
      setProjectType("unknown")
      goNext()
      expect(currentStep.value).toBe(1)
    })
  })
})
