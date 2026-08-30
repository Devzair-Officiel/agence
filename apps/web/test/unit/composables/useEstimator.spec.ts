import { describe, expect, it } from "vitest"
import { useEstimator } from "~/composables/useEstimator"

describe("useEstimator", () => {
  describe("initial state", () => {
    it("starts at step 1", () => {
      const { currentStep } = useEstimator()
      expect(currentStep.value).toBe(1)
    })

    it("projectType is null", () => {
      const { projectType } = useEstimator()
      expect(projectType.value).toBeNull()
    })

    it("all array fields are empty", () => {
      const { objectives, features, contentNeeds, visibilityNeeds, careNeeds } =
        useEstimator()
      expect(objectives.value).toEqual([])
      expect(features.value).toEqual([])
      expect(contentNeeds.value).toEqual([])
      expect(visibilityNeeds.value).toEqual([])
      expect(careNeeds.value).toEqual([])
    })

    it("currentSituation and scale are null", () => {
      const { currentSituation, scale } = useEstimator()
      expect(currentSituation.value).toBeNull()
      expect(scale.value).toBeNull()
    })

    it("canGoNext is false at step 1 with no project type", () => {
      const { canGoNext } = useEstimator()
      expect(canGoNext.value).toBe(false)
    })

    it("path defaults to standard when no type selected", () => {
      const { path } = useEstimator()
      expect(path.value).toBe("standard")
    })

    it("totalSteps defaults to 7 (standard path)", () => {
      const { totalSteps } = useEstimator()
      expect(totalSteps.value).toBe(7)
    })
  })

  describe("path and totalSteps", () => {
    it("path is unknown when projectType is unknown", () => {
      const { path, setProjectType } = useEstimator()
      setProjectType("unknown")
      expect(path.value).toBe("unknown")
    })

    it("totalSteps is 6 for unknown path", () => {
      const { totalSteps, setProjectType } = useEstimator()
      setProjectType("unknown")
      expect(totalSteps.value).toBe(6)
    })

    it("totalSteps is 7 for each standard type", () => {
      for (const type of [
        "vitrinesite",
        "ecommerce",
        "businessapp",
        "refonte",
        "other",
      ] as const) {
        const { totalSteps, setProjectType } = useEstimator()
        setProjectType(type)
        expect(totalSteps.value).toBe(7)
      }
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

    it("clears objectives, scale, features when type changes", () => {
      const { setProjectType, setObjectives, setFeatures, setScale, objectives, scale, features } =
        useEstimator()
      setProjectType("vitrinesite")
      setObjectives(["present_business"])
      setScale("small")
      setFeatures(["contact_form"])
      setProjectType("ecommerce")
      expect(objectives.value).toEqual([])
      expect(scale.value).toBeNull()
      expect(features.value).toEqual([])
    })

    it("does not clear currentSituation when type changes", () => {
      const { setProjectType, setCurrentSituation, currentSituation } =
        useEstimator()
      setProjectType("vitrinesite")
      setCurrentSituation("none")
      setProjectType("ecommerce")
      expect(currentSituation.value).toBe("none")
    })

    it("does not clear contentNeeds when type changes", () => {
      const { setProjectType, setContentNeeds, contentNeeds } = useEstimator()
      setProjectType("vitrinesite")
      setContentNeeds(["photography_needed"])
      setProjectType("ecommerce")
      expect(contentNeeds.value).toEqual(["photography_needed"])
    })

    it("does not clear when same type is re-selected", () => {
      const { setProjectType, setScale, scale } = useEstimator()
      setProjectType("vitrinesite")
      setScale("medium")
      setProjectType("vitrinesite")
      expect(scale.value).toBe("medium")
    })
  })

  describe("canGoNext — standard path", () => {
    it("step 1: true when projectType set", () => {
      const { canGoNext, setProjectType } = useEstimator()
      setProjectType("vitrinesite")
      expect(canGoNext.value).toBe(true)
    })

    it("step 2: false when currentSituation is null", () => {
      const { canGoNext, setProjectType, goNext } = useEstimator()
      setProjectType("vitrinesite")
      goNext()
      expect(canGoNext.value).toBe(false)
    })

    it("step 2: true when currentSituation is set", () => {
      const { canGoNext, setProjectType, setCurrentSituation, goNext } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      expect(canGoNext.value).toBe(true)
    })

    it("step 3: false when scale is null", () => {
      const { canGoNext, setProjectType, setCurrentSituation, goNext } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      goNext()
      expect(canGoNext.value).toBe(false)
    })

    it("step 3: true when scale is set", () => {
      const { canGoNext, setProjectType, setCurrentSituation, setScale, goNext } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      goNext()
      setScale("small")
      expect(canGoNext.value).toBe(true)
    })

    it("steps 4-7: always true (optional)", () => {
      const { canGoNext, setProjectType, setCurrentSituation, setScale, goNext } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      goNext()
      setScale("small")
      for (let i = 0; i < 4; i++) {
        goNext()
        expect(canGoNext.value).toBe(true)
      }
    })
  })

  describe("canGoNext — unknown path", () => {
    it("step 2: false when no objectives selected", () => {
      const { canGoNext, setProjectType, goNext } = useEstimator()
      setProjectType("unknown")
      goNext()
      expect(canGoNext.value).toBe(false)
    })

    it("step 2: true when at least one objective selected", () => {
      const { canGoNext, setProjectType, setObjectives, goNext } = useEstimator()
      setProjectType("unknown")
      goNext()
      setObjectives(["sell_online"])
      expect(canGoNext.value).toBe(true)
    })

    it("step 3: false when currentSituation is null", () => {
      const { canGoNext, setProjectType, setObjectives, goNext } = useEstimator()
      setProjectType("unknown")
      goNext()
      setObjectives(["present_business"])
      goNext()
      expect(canGoNext.value).toBe(false)
    })

    it("step 3: true when currentSituation is set", () => {
      const { canGoNext, setProjectType, setObjectives, setCurrentSituation, goNext } =
        useEstimator()
      setProjectType("unknown")
      goNext()
      setObjectives(["present_business"])
      goNext()
      setCurrentSituation("none")
      expect(canGoNext.value).toBe(true)
    })

    it("steps 4-6: always true", () => {
      const { canGoNext, setProjectType, setObjectives, setCurrentSituation, goNext } =
        useEstimator()
      setProjectType("unknown")
      goNext()
      setObjectives(["present_business"])
      goNext()
      setCurrentSituation("none")
      for (let i = 0; i < 3; i++) {
        goNext()
        expect(canGoNext.value).toBe(true)
      }
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
      setProjectType("vitrinesite")
      goNext()
      expect(currentStep.value).toBe(2)
    })

    it("does not exceed totalSteps (standard)", () => {
      const { currentStep, goNext, setProjectType, setCurrentSituation, setScale } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      goNext()
      setScale("small")
      for (let i = 0; i < 10; i++) goNext()
      expect(currentStep.value).toBe(7)
    })

    it("does not exceed totalSteps (unknown = 6)", () => {
      const { currentStep, goNext, setProjectType, setObjectives, setCurrentSituation } =
        useEstimator()
      setProjectType("unknown")
      goNext()
      setObjectives(["sell_online"])
      goNext()
      setCurrentSituation("none")
      for (let i = 0; i < 10; i++) goNext()
      expect(currentStep.value).toBe(6)
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

  describe("isComplete", () => {
    it("false while navigating standard path", () => {
      const { isComplete, setProjectType } = useEstimator()
      setProjectType("vitrinesite")
      expect(isComplete.value).toBe(false)
    })

    it("true on last step of standard path (step 7)", () => {
      const { isComplete, setProjectType, setCurrentSituation, setScale, goNext } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      goNext()
      setScale("small")
      for (let i = 0; i < 4; i++) goNext()
      expect(isComplete.value).toBe(true)
    })

    it("true on last step of unknown path (step 6)", () => {
      const { isComplete, setProjectType, setObjectives, setCurrentSituation, goNext } =
        useEstimator()
      setProjectType("unknown")
      goNext()
      setObjectives(["sell_online"])
      goNext()
      setCurrentSituation("none")
      for (let i = 0; i < 3; i++) goNext()
      expect(isComplete.value).toBe(true)
    })
  })

  describe("set methods", () => {
    it("setCurrentSituation updates currentSituation", () => {
      const { currentSituation, setCurrentSituation } = useEstimator()
      setCurrentSituation("has_website")
      expect(currentSituation.value).toBe("has_website")
    })

    it("setScale updates scale", () => {
      const { scale, setScale } = useEstimator()
      setScale("large")
      expect(scale.value).toBe("large")
    })

    it("setFeatures replaces features array", () => {
      const { features, setFeatures } = useEstimator()
      setFeatures(["contact_form", "payment"])
      expect(features.value).toEqual(["contact_form", "payment"])
      setFeatures(["multilingual"])
      expect(features.value).toEqual(["multilingual"])
    })

    it("setObjectives replaces objectives array", () => {
      const { objectives, setObjectives } = useEstimator()
      setObjectives(["sell_online", "generate_leads"])
      expect(objectives.value).toEqual(["sell_online", "generate_leads"])
    })

    it("setContentNeeds replaces contentNeeds array", () => {
      const { contentNeeds, setContentNeeds } = useEstimator()
      setContentNeeds(["visual_identity_needed", "photography_needed"])
      expect(contentNeeds.value).toEqual([
        "visual_identity_needed",
        "photography_needed",
      ])
    })

    it("setVisibilityNeeds replaces visibilityNeeds array", () => {
      const { visibilityNeeds, setVisibilityNeeds } = useEstimator()
      setVisibilityNeeds(["seo_basic", "local_visibility"])
      expect(visibilityNeeds.value).toEqual(["seo_basic", "local_visibility"])
    })

    it("setCareNeeds replaces careNeeds array", () => {
      const { careNeeds, setCareNeeds } = useEstimator()
      setCareNeeds(["maintenance", "support"])
      expect(careNeeds.value).toEqual(["maintenance", "support"])
    })

    it("setCareNeeds([]) represents autonomy mode", () => {
      const { careNeeds, setCareNeeds } = useEstimator()
      setCareNeeds(["maintenance"])
      setCareNeeds([])
      expect(careNeeds.value).toEqual([])
    })
  })

  describe("toEstimatePayload — standard path", () => {
    it("returns complete payload for standard path", () => {
      const {
        setProjectType,
        setCurrentSituation,
        setScale,
        setFeatures,
        setContentNeeds,
        setVisibilityNeeds,
        setCareNeeds,
        toEstimatePayload,
      } = useEstimator()

      setProjectType("vitrinesite")
      setCurrentSituation("none")
      setScale("medium")
      setFeatures(["contact_form", "booking_form"])
      setContentNeeds(["visual_identity_partial"])
      setVisibilityNeeds(["seo_basic"])
      setCareNeeds(["maintenance"])

      const payload = toEstimatePayload()
      expect(payload).toEqual({
        project_type: "vitrinesite",
        objectives: [],
        current_situation: "none",
        scale: "medium",
        features: ["contact_form", "booking_form"],
        content_needs: ["visual_identity_partial"],
        visibility_needs: ["seo_basic"],
        care_needs: ["maintenance"],
      })
    })

    it("uses 'unknown' for unset scale", () => {
      const { setProjectType, toEstimatePayload } = useEstimator()
      setProjectType("vitrinesite")
      const payload = toEstimatePayload()
      expect(payload.scale).toBe("unknown")
    })

    it("uses 'unknown' for unset currentSituation", () => {
      const { setProjectType, toEstimatePayload } = useEstimator()
      setProjectType("vitrinesite")
      const payload = toEstimatePayload()
      expect(payload.current_situation).toBe("unknown")
    })
  })

  describe("toEstimatePayload — unknown path", () => {
    it("sets scale to unknown and features to [] on unknown path", () => {
      const { setProjectType, setObjectives, setScale, setFeatures, toEstimatePayload } =
        useEstimator()
      setProjectType("unknown")
      setObjectives(["sell_online"])
      setScale("large")
      setFeatures(["payment"])
      const payload = toEstimatePayload()
      expect(payload.scale).toBe("unknown")
      expect(payload.features).toEqual([])
    })

    it("includes objectives in unknown path payload", () => {
      const { setProjectType, setObjectives, toEstimatePayload } = useEstimator()
      setProjectType("unknown")
      setObjectives(["generate_leads", "improve_visibility"])
      const payload = toEstimatePayload()
      expect(payload.objectives).toEqual(["generate_leads", "improve_visibility"])
    })

    it("returns defensive payload when no projectType set", () => {
      const { toEstimatePayload } = useEstimator()
      const payload = toEstimatePayload()
      expect(payload.project_type).toBe("unknown")
      expect(payload.scale).toBe("unknown")
      expect(payload.current_situation).toBe("unknown")
    })
  })

  describe("payload immutability", () => {
    it("toEstimatePayload returns a copy of array fields", () => {
      const { setProjectType, setFeatures, toEstimatePayload, features } =
        useEstimator()
      setProjectType("vitrinesite")
      setFeatures(["contact_form"])
      const payload = toEstimatePayload()
      payload.features.push("payment" as never)
      expect(features.value).toEqual(["contact_form"])
    })
  })

  describe("additionalFeatureNote", () => {
    it("initialises to empty string", () => {
      const { additionalFeatureNote } = useEstimator()
      expect(additionalFeatureNote.value).toBe("")
    })

    it("setAdditionalFeatureNote updates the value", () => {
      const { additionalFeatureNote, setAdditionalFeatureNote } = useEstimator()
      setAdditionalFeatureNote("Système de chat en temps réel")
      expect(additionalFeatureNote.value).toBe("Système de chat en temps réel")
    })

    it("is not included in toEstimatePayload", () => {
      const { setProjectType, setAdditionalFeatureNote, toEstimatePayload } =
        useEstimator()
      setProjectType("vitrinesite")
      setAdditionalFeatureNote("Besoin secret")
      const payload = toEstimatePayload()
      expect(Object.keys(payload)).not.toContain("additional_feature_note")
      expect(Object.keys(payload)).not.toContain("additionalFeatureNote")
    })

    it("is cleared when switching to unknown path", () => {
      const { additionalFeatureNote, setProjectType, setAdditionalFeatureNote } =
        useEstimator()
      setProjectType("vitrinesite")
      setAdditionalFeatureNote("Une idée")
      setProjectType("unknown")
      expect(additionalFeatureNote.value).toBe("")
    })

    it("is preserved when switching between standard types", () => {
      const { additionalFeatureNote, setProjectType, setAdditionalFeatureNote } =
        useEstimator()
      setProjectType("vitrinesite")
      setAdditionalFeatureNote("Une idée")
      setProjectType("ecommerce")
      expect(additionalFeatureNote.value).toBe("Une idée")
    })

    it("is preserved when same type is re-selected", () => {
      const { additionalFeatureNote, setProjectType, setAdditionalFeatureNote } =
        useEstimator()
      setProjectType("vitrinesite")
      setAdditionalFeatureNote("Une idée")
      setProjectType("vitrinesite")
      expect(additionalFeatureNote.value).toBe("Une idée")
    })
  })

  describe("careAnswered", () => {
    it("initialises to false", () => {
      const { careAnswered } = useEstimator()
      expect(careAnswered.value).toBe(false)
    })

    it("becomes true after setCareNeeds is called", () => {
      const { careAnswered, setCareNeeds } = useEstimator()
      setCareNeeds(["maintenance"])
      expect(careAnswered.value).toBe(true)
    })

    it("is true even when setCareNeeds called with empty array", () => {
      const { careAnswered, setCareNeeds } = useEstimator()
      setCareNeeds([])
      expect(careAnswered.value).toBe(true)
    })

    it("resets to false when project type changes", () => {
      const { careAnswered, setCareNeeds, setProjectType } = useEstimator()
      setProjectType("vitrinesite")
      setCareNeeds(["support"])
      setProjectType("ecommerce")
      expect(careAnswered.value).toBe(false)
    })

    it("is NOT reset when same type is re-selected", () => {
      const { careAnswered, setCareNeeds, setProjectType } = useEstimator()
      setProjectType("vitrinesite")
      setCareNeeds(["support"])
      setProjectType("vitrinesite")
      expect(careAnswered.value).toBe(true)
    })
  })

  describe("maxVisitedStep", () => {
    it("initialises to 1", () => {
      const { maxVisitedStep } = useEstimator()
      expect(maxVisitedStep.value).toBe(1)
    })

    it("is updated by goNext", () => {
      const { maxVisitedStep, setProjectType, goNext } = useEstimator()
      setProjectType("vitrinesite")
      goNext()
      expect(maxVisitedStep.value).toBe(2)
    })

    it("never goes below a previously reached step on goPrev", () => {
      const { maxVisitedStep, setProjectType, setCurrentSituation, goNext, goPrev } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      goNext()
      expect(maxVisitedStep.value).toBe(3)
      goPrev()
      expect(maxVisitedStep.value).toBe(3)
    })

    it("resets to 1 when project type changes", () => {
      const { maxVisitedStep, setProjectType, setCurrentSituation, goNext } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      goNext()
      expect(maxVisitedStep.value).toBe(3)
      setProjectType("ecommerce")
      expect(maxVisitedStep.value).toBe(1)
    })

    it("is NOT reset when same type is re-selected", () => {
      const { maxVisitedStep, setProjectType, setCurrentSituation, goNext } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      goNext()
      setProjectType("vitrinesite")
      expect(maxVisitedStep.value).toBe(3)
    })
  })

  describe("goToStep", () => {
    it("navigates to a previously visited step", () => {
      const { currentStep, maxVisitedStep, setProjectType, setCurrentSituation, goNext, goToStep } =
        useEstimator()
      setProjectType("vitrinesite")
      goNext()
      setCurrentSituation("none")
      goNext()
      expect(maxVisitedStep.value).toBe(3)
      goToStep(1)
      expect(currentStep.value).toBe(1)
    })

    it("does nothing when step > maxVisitedStep", () => {
      const { currentStep, setProjectType, goToStep } = useEstimator()
      setProjectType("vitrinesite")
      expect(currentStep.value).toBe(1)
      goToStep(3)
      expect(currentStep.value).toBe(1)
    })

    it("does nothing when step < 1", () => {
      const { currentStep, goToStep } = useEstimator()
      goToStep(0)
      expect(currentStep.value).toBe(1)
    })

    it("can navigate to maxVisitedStep itself", () => {
      const { currentStep, setProjectType, goNext, goToStep } = useEstimator()
      setProjectType("vitrinesite")
      goNext()
      expect(currentStep.value).toBe(2)
      goToStep(1)
      expect(currentStep.value).toBe(1)
      goToStep(2)
      expect(currentStep.value).toBe(2)
    })
  })
})
