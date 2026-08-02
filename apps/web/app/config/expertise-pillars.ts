/**
 * Les cinq pôles d'expertise Devzair — source de vérité unique.
 *
 * Consommée par HomeHero (réassurance), HomeEcosystemGraph (SVG central),
 * HomeConnectedApproach (parcours 5 étapes), HomeExpertisePillars (grille
 * détaillée) et la future page /expertises.
 *
 * `shortLabel` est autorisé UNIQUEMENT pour un rendu contraint en espace
 * (ex. libellé au-dessus d'un pôle du graphe SVG). Le lecteur d'écran et
 * la description longue utilisent toujours `label` complet.
 *
 * `description` est le libellé court affiché DANS le graphe SVG
 * (« design · UX · identité »). `longDescription` est la phrase narrative
 * publiée par la grille détaillée. `services` liste les 3 livrables
 * associés au pôle, rendus dans la carte correspondante.
 *
 * `variant` :
 * - `primary`  → Construire (accent visuel dans le SVG hero) ;
 * - `accent`   → Faire évoluer (fond petrol dans la grille détaillée) ;
 * - `default`  → autres pôles.
 */

export type ExpertisePillarVariant = "primary" | "default" | "accent"

export interface ExpertisePillar {
  readonly id: string
  readonly label: string
  readonly shortLabel: string
  readonly description: string
  readonly longDescription: string
  readonly services: readonly string[]
  readonly order: number
  readonly variant: ExpertisePillarVariant
}

export const expertisePillars: readonly ExpertisePillar[] = [
  {
    id: "concevoir",
    label: "Concevoir",
    shortLabel: "Concevoir",
    description: "design · UX · identité",
    longDescription:
      "Nous posons les fondations visuelles et fonctionnelles avant tout développement : identité, architecture d'information, parcours et maquettes valident les intentions en amont.",
    services: [
      "Identité de marque",
      "Design d'interface",
      "Architecture d'information",
    ],
    order: 1,
    variant: "default",
  },
  {
    id: "construire",
    label: "Construire",
    shortLabel: "Construire",
    description: "site · e-commerce · application",
    longDescription:
      "Nous développons les sites, plateformes e-commerce et applications métier sur des bases techniques modernes, testées et pensées pour durer.",
    services: [
      "Site vitrine sur mesure",
      "Plateforme e-commerce",
      "Application métier",
    ],
    order: 2,
    variant: "primary",
  },
  {
    id: "valoriser",
    label: "Valoriser",
    shortLabel: "Valoriser",
    description: "photographie · contenu",
    longDescription:
      "Nous produisons les contenus visuels et éditoriaux qui rendent le message crédible : photographies, prises de vue, textes, structuration de l'offre.",
    services: [
      "Photographie professionnelle",
      "Contenus rédactionnels",
      "Structuration de l'offre",
    ],
    order: 3,
    variant: "default",
  },
  {
    id: "visibilite",
    label: "Développer la visibilité",
    shortLabel: "Visibilité",
    description: "SEO · local · éditorial",
    longDescription:
      "Nous travaillons la visibilité durable : référencement naturel, présence locale, contenus éditoriaux orientés intention de recherche et lisibilité par les moteurs IA.",
    services: [
      "Référencement naturel (SEO)",
      "Visibilité locale",
      "Stratégie éditoriale",
    ],
    order: 4,
    variant: "default",
  },
  {
    id: "faire-evoluer",
    label: "Faire évoluer",
    shortLabel: "Faire évoluer",
    description: "suivi · maintenance",
    longDescription:
      "Nous assurons le suivi dans le temps : mesures, mises à jour, sécurité, évolutions fonctionnelles. Un site vivant, tenu à jour, aligné sur vos priorités.",
    services: [
      "Maintenance et sécurité",
      "Mesure et amélioration continue",
      "Évolutions fonctionnelles",
    ],
    order: 5,
    variant: "accent",
  },
]
