import type { VisibilityNeedCode } from "~/types/estimator"

export interface EstimatorVisibilityOption {
  readonly value: VisibilityNeedCode
  readonly label: string
  readonly description: string
}

// Codes alignés sur VisibilityNeed.php.
export const ESTIMATOR_VISIBILITY_OPTIONS: readonly EstimatorVisibilityOption[] = [
  {
    value: "seo_basic",
    label: "Référencement de base",
    description: "Balises, structure HTML et vitesse de chargement : les fondations du SEO.",
  },
  {
    value: "seo_advanced",
    label: "Référencement avancé",
    description: "Stratégie de mots-clés, maillage interne, suivi de performances.",
  },
  {
    value: "local_visibility",
    label: "Visibilité locale",
    description: "Google Business Profile, SEO local, gestion des avis clients.",
  },
  {
    value: "editorial_strategy",
    label: "Stratégie éditoriale",
    description: "Calendrier de contenu, articles de blog SEO, newsletters.",
  },
]
