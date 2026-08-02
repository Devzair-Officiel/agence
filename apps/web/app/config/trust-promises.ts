/**
 * Cinq promesses Devzair — source de vérité unique.
 *
 * Consommée par `HomeTrust.vue` (section « Pourquoi Devzair » de la home).
 * Chaque promesse décrit une pratique observable, jamais un superlatif
 * (« meilleur », « unique »…). Le texte est verbatim celui du brief Phase 5C.
 */

export interface TrustPromise {
  readonly id: string
  readonly label: string
  readonly description: string
  readonly order: number
}

export const trustPromises: readonly TrustPromise[] = [
  {
    id: "approche-personnalisee",
    label: "Approche personnalisée",
    description:
      "Chaque projet démarre par une compréhension réelle de votre activité. Nous ne dupliquons pas des solutions génériques d'un client à l'autre.",
    order: 1,
  },
  {
    id: "vision-globale",
    label: "Vision globale",
    description:
      "Design, développement, contenu, SEO et suivi sont pensés ensemble, pas empilés comme cinq prestations indépendantes.",
    order: 2,
  },
  {
    id: "qualite-technique",
    label: "Qualité technique",
    description:
      "Bases modernes, code testé, performance, sécurité et accessibilité contrôlées à chaque étape, pas seulement à la livraison.",
    order: 3,
  },
  {
    id: "transparence",
    label: "Transparence",
    description:
      "Un interlocuteur clair, un périmètre écrit, des décisions tracées : vous savez à tout moment où en est le projet et pourquoi.",
    order: 4,
  },
  {
    id: "accompagnement-durable",
    label: "Accompagnement durable",
    description:
      "Nous restons disponibles après la mise en ligne pour mesurer, corriger et faire évoluer le site en fonction de vos priorités.",
    order: 5,
  },
]
