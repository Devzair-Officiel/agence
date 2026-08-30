import { computed, ref } from "vue"
import type { ComputedRef, Ref } from "vue"

import type { ProjectTypeCode } from "~/config/estimator-project-types"

export interface UseEstimatorOptions {
  maxStep?: number
}

export interface EstimatorApi {
  currentStep: Ref<number>
  projectType: Ref<ProjectTypeCode | null>
  canGoNext: ComputedRef<boolean>
  goNext: () => void
  goPrev: () => void
  setProjectType: (value: ProjectTypeCode) => void
}

export function useEstimator(options: UseEstimatorOptions = {}): EstimatorApi {
  const maxStep = options.maxStep ?? 7

  const currentStep = ref<number>(1)
  const projectType = ref<ProjectTypeCode | null>(null)

  const canGoNext = computed<boolean>(() => {
    if (currentStep.value === 1) return projectType.value !== null
    return true
  })

  function goNext(): void {
    if (!canGoNext.value) return
    if (currentStep.value >= maxStep) return
    currentStep.value++
  }

  function goPrev(): void {
    if (currentStep.value <= 1) return
    currentStep.value--
  }

  function setProjectType(value: ProjectTypeCode): void {
    projectType.value = value
  }

  return {
    currentStep,
    projectType,
    canGoNext,
    goNext,
    goPrev,
    setProjectType,
  }
}
