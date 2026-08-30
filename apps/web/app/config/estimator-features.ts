import type { ProjectFeatureCode, ProjectTypeCode } from "~/types/estimator"

export interface EstimatorFeature {
  readonly value: ProjectFeatureCode
  readonly label: string
  readonly description: string
}

export const ALL_FEATURES: readonly EstimatorFeature[] = [
  {
    value: "contact_form",
    label: "Formulaire de contact",
    description: "Formulaire simple ou multi-étapes pour recevoir des demandes.",
  },
  {
    value: "booking_form",
    label: "Prise de rendez-vous",
    description: "Intégration d'un outil de réservation (Calendly ou équivalent).",
  },
  {
    value: "multilingual",
    label: "Site multilingue",
    description: "Contenu disponible en plusieurs langues.",
  },
  {
    value: "payment",
    label: "Paiement en ligne",
    description: "Accepter des paiements par carte bancaire ou autre.",
  },
  {
    value: "shipping",
    label: "Gestion des livraisons",
    description: "Calcul des frais, modes d'expédition, suivi de commande.",
  },
  {
    value: "customer_accounts",
    label: "Espace client",
    description: "Comptes clients avec historique des commandes ou contenus personnalisés.",
  },
  {
    value: "authentication",
    label: "Authentification utilisateur",
    description: "Connexion sécurisée, gestion des sessions.",
  },
  {
    value: "roles_and_permissions",
    label: "Rôles et permissions",
    description: "Plusieurs niveaux d'accès selon le profil utilisateur.",
  },
  {
    value: "content_management",
    label: "Gestion de contenu (CMS)",
    description: "Back-office pour modifier pages, actualités ou données sans intervention technique.",
  },
  {
    value: "document_generation",
    label: "Génération de documents",
    description: "Création automatique de PDFs, contrats, devis ou rapports.",
  },
  {
    value: "notifications",
    label: "Notifications",
    description: "Alertes par email, dans l'application ou par SMS.",
  },
  {
    value: "import_export",
    label: "Import / export de données",
    description: "Chargement ou extraction de fichiers Excel, CSV ou via API.",
  },
  {
    value: "api_integration",
    label: "Intégration API / outils tiers",
    description: "Connexion à des outils existants : CRM, ERP, emailing, comptabilité…",
  },
  {
    value: "stock_management",
    label: "Gestion des stocks",
    description: "Suivi des quantités, alertes de rupture, inventaire.",
  },
  {
    value: "promotions",
    label: "Promotions et codes promo",
    description: "Gestion des soldes, remises et codes de réduction.",
  },
]

// Sous-ensemble de features pertinentes par type de projet.
// Règle : ne pas afficher des features manifestement inapplicables,
// mais ne pas créer de règle artificielle d'exclusion.
const FEATURES_BY_TYPE: Record<ProjectTypeCode, readonly ProjectFeatureCode[]> = {
  vitrinesite: [
    "contact_form",
    "booking_form",
    "multilingual",
    "content_management",
    "api_integration",
  ],
  ecommerce: [
    "payment",
    "shipping",
    "customer_accounts",
    "stock_management",
    "promotions",
    "multilingual",
    "api_integration",
    "import_export",
  ],
  businessapp: [
    "authentication",
    "roles_and_permissions",
    "document_generation",
    "notifications",
    "import_export",
    "api_integration",
    "content_management",
    "payment",
  ],
  refonte: ALL_FEATURES.map((f) => f.value),
  other: ALL_FEATURES.map((f) => f.value),
  unknown: ALL_FEATURES.map((f) => f.value),
}

export function getFeaturesForType(
  projectType: ProjectTypeCode,
): readonly EstimatorFeature[] {
  const codes = FEATURES_BY_TYPE[projectType]
  return ALL_FEATURES.filter((f) => codes.includes(f.value))
}
