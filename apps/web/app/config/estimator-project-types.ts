export type ProjectTypeCode =
  | "vitrinesite"
  | "ecommerce"
  | "businessapp"
  | "refonte"
  | "other"
  | "unknown"

export interface EstimatorProjectType {
  readonly value: ProjectTypeCode
  readonly label: string
  readonly description: string
}

export const ESTIMATOR_PROJECT_TYPES: readonly EstimatorProjectType[] = [
  {
    value: "vitrinesite",
    label: "Site vitrine / institutionnel",
    description: "Présenter votre activité, vos services et votre équipe.",
  },
  {
    value: "ecommerce",
    label: "Boutique en ligne",
    description: "Vendre vos produits ou services directement sur internet.",
  },
  {
    value: "businessapp",
    label: "Application métier",
    description:
      "Un outil sur mesure pour piloter un processus interne ou client.",
  },
  {
    value: "refonte",
    label: "Refonte de site existant",
    description:
      "Moderniser ou reconstruire un site déjà en ligne, en conservant l'acquis.",
  },
  {
    value: "other",
    label: "Autre projet digital",
    description:
      "Une idée qui sort des cases habituelles — racontez-nous, nous écoutons.",
  },
  {
    value: "unknown",
    label: "Je ne sais pas encore",
    description:
      "Pas de problème : la session de cadrage est faite pour ça. Nous clarifierons ensemble.",
  },
] as const
