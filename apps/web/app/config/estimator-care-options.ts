import type { CareNeedCode } from "~/types/estimator"

export interface EstimatorCareOption {
  readonly value: CareNeedCode
  readonly label: string
  readonly description: string
}

// Codes alignés sur CareNeed.php.
// Un tableau vide (aucune sélection) signifie que le client gère en autonomie.
export const ESTIMATOR_CARE_OPTIONS: readonly EstimatorCareOption[] = [
  {
    value: "maintenance",
    label: "Maintenance technique",
    description: "Mises à jour de sécurité, sauvegardes, monitoring.",
  },
  {
    value: "content_updates",
    label: "Mise à jour du contenu",
    description: "Ajout d'articles, modification de textes, publication régulière.",
  },
  {
    value: "continuous_seo",
    label: "SEO continu",
    description: "Optimisations régulières, rapport de performances, ajustements.",
  },
  {
    value: "support",
    label: "Support et assistance",
    description: "Réponse aux questions, résolution de problèmes ponctuels.",
  },
  {
    value: "functional_evolution",
    label: "Évolutions fonctionnelles",
    description: "Ajout de nouvelles fonctionnalités au fil du temps.",
  },
]
