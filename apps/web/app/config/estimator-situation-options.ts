import type { CurrentSituationCode } from "~/types/estimator"

export interface EstimatorSituationOption {
  readonly value: CurrentSituationCode
  readonly label: string
  readonly description: string
}

// Mapping UX → CurrentSituation.php (5 codes backend).
//
// ÉCART DOCUMENTÉ : le doc §6 étape 02 propose seulement "Oui / Non / Je ne sais pas".
// Le backend distingue 3 types d'existant (has_website, has_ecommerce, has_business_app).
// L'UX est étendue pour couvrir les 5 cas backend et fournir un signal plus précis au moteur.
//
// HORIZON NON COLLECTÉ : le doc §6 étape 02 mentionne "Quel est l'horizon de votre projet".
// ProjectEstimateInput n'a pas de champ horizon. Le doc lui-même note que l'horizon
// "influe sur la priorisation mais pas nécessairement sur la fourchette budgétaire"
// et recommande de ne pas le conserver (principe KISS). → Horizon non collecté en EST-3.
// Documenter pour une priorisation future si un champ backend est ajouté.
export const ESTIMATOR_SITUATION_OPTIONS: readonly EstimatorSituationOption[] = [
  {
    value: "none",
    label: "Non, c'est une création",
    description: "Vous n'avez pas encore de présence en ligne pour ce projet.",
  },
  {
    value: "has_website",
    label: "Oui, un site vitrine ou blog",
    description: "Vous avez déjà un site de présentation que vous souhaitez améliorer ou remplacer.",
  },
  {
    value: "has_ecommerce",
    label: "Oui, une boutique en ligne",
    description: "Vous avez déjà une boutique e-commerce en production.",
  },
  {
    value: "has_business_app",
    label: "Oui, une application ou outil métier",
    description: "Vous avez une application existante que vous souhaitez faire évoluer.",
  },
  {
    value: "unknown",
    label: "Je ne sais pas encore",
    description: "Pas de problème, nous clarifierons cela lors du cadrage.",
  },
] as const
