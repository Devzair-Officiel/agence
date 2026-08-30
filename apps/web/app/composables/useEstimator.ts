import { computed, ref } from "vue"
import type { ComputedRef, Ref } from "vue"
import type {
  CareNeedCode,
  ContentNeedCode,
  CurrentSituationCode,
  EstimatePayload,
  ProjectFeatureCode,
  ProjectObjectiveCode,
  ProjectScaleCode,
  ProjectTypeCode,
  VisibilityNeedCode,
} from "~/types/estimator"

export type EstimatorPath = "standard" | "unknown"

export interface EstimatorApi {
  currentStep: Ref<number>
  projectType: Ref<ProjectTypeCode | null>
  objectives: Ref<ProjectObjectiveCode[]>
  currentSituation: Ref<CurrentSituationCode | null>
  scale: Ref<ProjectScaleCode | null>
  features: Ref<ProjectFeatureCode[]>
  contentNeeds: Ref<ContentNeedCode[]>
  visibilityNeeds: Ref<VisibilityNeedCode[]>
  careNeeds: Ref<CareNeedCode[]>
  path: ComputedRef<EstimatorPath>
  totalSteps: ComputedRef<number>
  canGoNext: ComputedRef<boolean>
  isComplete: ComputedRef<boolean>
  goNext: () => void
  goPrev: () => void
  setProjectType: (value: ProjectTypeCode) => void
  setCurrentSituation: (value: CurrentSituationCode) => void
  setScale: (value: ProjectScaleCode) => void
  setFeatures: (codes: ProjectFeatureCode[]) => void
  setObjectives: (codes: ProjectObjectiveCode[]) => void
  setContentNeeds: (codes: ContentNeedCode[]) => void
  setVisibilityNeeds: (codes: VisibilityNeedCode[]) => void
  setCareNeeds: (codes: CareNeedCode[]) => void
  toEstimatePayload: () => EstimatePayload
}

export function useEstimator(): EstimatorApi {
  const currentStep = ref<number>(1)
  const projectType = ref<ProjectTypeCode | null>(null)
  const objectives = ref<ProjectObjectiveCode[]>([])
  const currentSituation = ref<CurrentSituationCode | null>(null)
  const scale = ref<ProjectScaleCode | null>(null)
  const features = ref<ProjectFeatureCode[]>([])
  const contentNeeds = ref<ContentNeedCode[]>([])
  const visibilityNeeds = ref<VisibilityNeedCode[]>([])
  const careNeeds = ref<CareNeedCode[]>([])

  const path = computed<EstimatorPath>(() =>
    projectType.value === "unknown" ? "unknown" : "standard",
  )

  const totalSteps = computed<number>(() => (path.value === "unknown" ? 6 : 7))

  const canGoNext = computed<boolean>(() => {
    const step = currentStep.value
    if (step === 1) return projectType.value !== null
    if (path.value === "standard") {
      if (step === 2) return currentSituation.value !== null
      if (step === 3) return scale.value !== null
      return true
    } else {
      if (step === 2) return objectives.value.length > 0
      if (step === 3) return currentSituation.value !== null
      return true
    }
  })

  const isComplete = computed<boolean>(
    () => currentStep.value >= totalSteps.value,
  )

  function goNext(): void {
    if (!canGoNext.value) return
    if (currentStep.value >= totalSteps.value) return
    currentStep.value++
  }

  function goPrev(): void {
    if (currentStep.value <= 1) return
    currentStep.value--
  }

  function setProjectType(value: ProjectTypeCode): void {
    if (projectType.value !== value) {
      objectives.value = []
      scale.value = null
      features.value = []
    }
    projectType.value = value
  }

  function setCurrentSituation(value: CurrentSituationCode): void {
    currentSituation.value = value
  }

  function setScale(value: ProjectScaleCode): void {
    scale.value = value
  }

  function setFeatures(codes: ProjectFeatureCode[]): void {
    features.value = codes
  }

  function setObjectives(codes: ProjectObjectiveCode[]): void {
    objectives.value = codes
  }

  function setContentNeeds(codes: ContentNeedCode[]): void {
    contentNeeds.value = codes
  }

  function setVisibilityNeeds(codes: VisibilityNeedCode[]): void {
    visibilityNeeds.value = codes
  }

  function setCareNeeds(codes: CareNeedCode[]): void {
    careNeeds.value = codes
  }

  function toEstimatePayload(): EstimatePayload {
    const isUnknown = path.value === "unknown"
    return {
      project_type: projectType.value ?? "unknown",
      objectives: [...objectives.value],
      current_situation: currentSituation.value ?? "unknown",
      scale: isUnknown ? "unknown" : (scale.value ?? "unknown"),
      features: isUnknown ? [] : [...features.value],
      content_needs: [...contentNeeds.value],
      visibility_needs: [...visibilityNeeds.value],
      care_needs: [...careNeeds.value],
    }
  }

  return {
    currentStep,
    projectType,
    objectives,
    currentSituation,
    scale,
    features,
    contentNeeds,
    visibilityNeeds,
    careNeeds,
    path,
    totalSteps,
    canGoNext,
    isComplete,
    goNext,
    goPrev,
    setProjectType,
    setCurrentSituation,
    setScale,
    setFeatures,
    setObjectives,
    setContentNeeds,
    setVisibilityNeeds,
    setCareNeeds,
    toEstimatePayload,
  }
}
