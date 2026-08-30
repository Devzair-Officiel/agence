import type { ProjectScaleCode, ProjectTypeCode } from "~/types/estimator"

export interface EstimatorScaleOption {
  readonly value: ProjectScaleCode
  readonly label: string
  readonly description: string
}

export interface EstimatorScaleConfig {
  readonly legend: string
  readonly options: readonly EstimatorScaleOption[]
}

// Labels et légende de l'étape Périmètre adaptés par type de projet.
// Codes alignés sur ProjectScale.php (small, medium, large, unknown).
//
// ÉCART DOCUMENTÉ (businessapp) : le doc §8.3 décrit le périmètre via des
// dimensions métier (utilisateurs, rôles, workflows). ProjectScale reste
// générique. L'UX utilise des libellés de "complexité fonctionnelle" plutôt
// que de "nombre de pages" pour businessapp. Les features spécifiques (auth,
// rôles, workflows, etc.) sont capturées à l'étape 4.
const SCALE_OPTIONS_BY_TYPE: Record<ProjectTypeCode, EstimatorScaleConfig> = {
  vitrinesite: {
    legend: "Quelle est l'ampleur de votre site ?",
    options: [
      {
        value: "small",
        label: "1 à 5 pages",
        description: "Accueil, à propos, services, contact — l'essentiel.",
      },
      {
        value: "medium",
        label: "6 à 15 pages",
        description: "Site structuré avec plusieurs sections, blog ou actualités.",
      },
      {
        value: "large",
        label: "Plus de 15 pages",
        description: "Site étendu avec contenu riche, catalogue de services ou portfolio.",
      },
      {
        value: "unknown",
        label: "Je ne sais pas encore",
        description: "Nous définirons le périmètre ensemble lors du cadrage.",
      },
    ],
  },

  ecommerce: {
    legend: "Quelle est la taille de votre catalogue ?",
    options: [
      {
        value: "small",
        label: "Moins de 50 produits",
        description: "Boutique légère, catalogue concentré.",
      },
      {
        value: "medium",
        label: "50 à 500 produits",
        description: "Catalogue intermédiaire, gestion de catégories.",
      },
      {
        value: "large",
        label: "Plus de 500 produits",
        description: "Grand catalogue, variantes, imports et gestion avancée.",
      },
      {
        value: "unknown",
        label: "Je ne sais pas encore",
        description: "Nous estimerons la taille ensemble.",
      },
    ],
  },

  businessapp: {
    legend: "Quel est le périmètre fonctionnel de l'application ?",
    options: [
      {
        value: "small",
        label: "Périmètre restreint",
        description: "Quelques fonctionnalités ciblées, un ou deux modules.",
      },
      {
        value: "medium",
        label: "Périmètre intermédiaire",
        description: "Plusieurs modules, gestion utilisateurs, workflows.",
      },
      {
        value: "large",
        label: "Périmètre étendu",
        description: "Plateforme complète, intégrations multiples, volumes importants.",
      },
      {
        value: "unknown",
        label: "Je ne sais pas encore",
        description: "Nous cadrerons le périmètre lors d'un premier échange.",
      },
    ],
  },

  refonte: {
    legend: "Quelle est l'ampleur de la refonte ?",
    options: [
      {
        value: "small",
        label: "Retouches ciblées",
        description: "Corrections ponctuelles, mise à jour de design, ajout de fonctionnalités mineures.",
      },
      {
        value: "medium",
        label: "Refonte partielle",
        description: "Modernisation du design et de certaines fonctionnalités, conservation de la structure.",
      },
      {
        value: "large",
        label: "Refonte complète",
        description: "Reconstruction intégrale, nouvelle technologie ou nouvelle architecture.",
      },
      {
        value: "unknown",
        label: "Je ne sais pas encore",
        description: "Nous évaluerons l'existant ensemble.",
      },
    ],
  },

  other: {
    legend: "Quelle est l'envergure de votre projet ?",
    options: [
      {
        value: "small",
        label: "Projet ponctuel",
        description: "Intervention ciblée, réalisable rapidement.",
      },
      {
        value: "medium",
        label: "Projet de taille intermédiaire",
        description: "Quelques semaines à quelques mois de travail.",
      },
      {
        value: "large",
        label: "Projet conséquent",
        description: "Projet structurant, plusieurs mois de développement.",
      },
      {
        value: "unknown",
        label: "Je ne sais pas encore",
        description: "Nous le définissons ensemble.",
      },
    ],
  },

  // Pour la branche unknown, l'étape scale est conservée mais avec une question générique.
  unknown: {
    legend: "Quelle est l'envergure estimée de votre projet ?",
    options: [
      {
        value: "small",
        label: "Projet léger",
        description: "Quelques pages, fonctionnalités limitées.",
      },
      {
        value: "medium",
        label: "Projet intermédiaire",
        description: "Périmètre standard, plusieurs fonctionnalités.",
      },
      {
        value: "large",
        label: "Projet ambitieux",
        description: "Plateforme complète, nombreuses fonctionnalités.",
      },
      {
        value: "unknown",
        label: "Je ne sais pas encore",
        description: "Pas d'inquiétude, nous clarifierons ensemble.",
      },
    ],
  },
}

export function getScaleConfig(projectType: ProjectTypeCode): EstimatorScaleConfig {
  return SCALE_OPTIONS_BY_TYPE[projectType]
}
