import type { ProjectObjectiveCode } from "~/types/estimator"

export interface EstimatorObjective {
  readonly value: ProjectObjectiveCode
  readonly label: string
  readonly description: string
}

// Objectifs pour la branche « Je ne sais pas encore » (objectif-first).
// Codes alignés sur ProjectObjective.php.
//
// ÉCART DOCUMENTÉ : le doc §7 mentionne "Autre (champ libre optionnel)" et
// "Plusieurs de ces objectifs" comme options distinctes. Le backend
// ProjectObjective n'a pas de code "other" ni "multiple". La multi-sélection
// est gérée nativement par la liste. Le champ libre "Autre" est différé
// à EST-6 (aucun champ libre transmis sans destination backend).
export const ESTIMATOR_OBJECTIVES: readonly EstimatorObjective[] = [
  {
    value: "present_business",
    label: "Présenter mon activité en ligne",
    description: "Site vitrine, présentation de votre entreprise, de vos services ou de votre équipe.",
  },
  {
    value: "sell_online",
    label: "Vendre des produits ou services en ligne",
    description: "Boutique, paiement en ligne, gestion des commandes.",
  },
  {
    value: "generate_leads",
    label: "Obtenir plus de contacts ou de rendez-vous",
    description: "Formulaires, prise de rendez-vous, demandes de devis.",
  },
  {
    value: "digitalize_process",
    label: "Digitaliser ou automatiser un processus interne",
    description: "Application métier, outil de gestion, workflow automatisé.",
  },
  {
    value: "improve_existing",
    label: "Améliorer un site ou une solution existante",
    description: "Refonte, mise à jour, ajout de fonctionnalités sur l'existant.",
  },
  {
    value: "improve_visibility",
    label: "Améliorer ma visibilité sur les moteurs de recherche",
    description: "Référencement naturel, présence locale, stratégie éditoriale.",
  },
] as const
