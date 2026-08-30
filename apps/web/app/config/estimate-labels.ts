/**
 * Labels français pour les codes retournés par POST /api/estimate.
 *
 * Sources de vérité backend :
 * - ProjectType          : apps/api/src/Estimator/Domain/ProjectType.php
 * - EstimateAssumption   : apps/api/src/Estimator/Application/Pricing/ProjectEstimationEngine.php
 * - Line item codes      : apps/api/src/Estimator/Application/Pricing/DevzairPricingCatalogV1.php
 */

// ─── Investissement initial ───────────────────────────────────────────────────

export const LINE_ITEM_LABELS: Record<string, string> = {
  // Socle
  BASE: "Conception et développement",
  // Features
  FEATURE_CONTACT_FORM: "Formulaire de contact",
  FEATURE_BOOKING_FORM: "Prise de rendez-vous en ligne",
  FEATURE_MULTILINGUAL: "Site multilingue",
  FEATURE_PAYMENT: "Paiement en ligne",
  FEATURE_SHIPPING: "Gestion des livraisons",
  FEATURE_CUSTOMER_ACCOUNTS: "Espace client",
  FEATURE_AUTHENTICATION: "Authentification utilisateur",
  FEATURE_ROLES_AND_PERMISSIONS: "Rôles et permissions",
  FEATURE_CONTENT_MANAGEMENT: "Gestion de contenu (CMS)",
  FEATURE_DOCUMENT_GENERATION: "Génération de documents",
  FEATURE_NOTIFICATIONS: "Notifications",
  FEATURE_IMPORT_EXPORT: "Import / export de données",
  FEATURE_API_INTEGRATION: "Intégration API / outils tiers",
  FEATURE_STOCK_MANAGEMENT: "Gestion des stocks",
  FEATURE_PROMOTIONS: "Promotions et codes promo",
  // Contenus
  CONTENT_VISUAL_IDENTITY_NEEDED: "Création d'identité visuelle",
  CONTENT_VISUAL_IDENTITY_PARTIAL: "Complétion de l'identité visuelle",
  CONTENT_CONTENT_TO_WRITE: "Rédaction de contenus",
  CONTENT_CONTENT_WITH_HELP: "Accompagnement éditorial",
  CONTENT_PHOTOGRAPHY_NEEDED: "Photographie professionnelle",
  // Visibilité
  VISIBILITY_SEO_ADVANCED: "Référencement naturel avancé",
  VISIBILITY_LOCAL_VISIBILITY: "Visibilité locale (Google Business)",
  VISIBILITY_EDITORIAL_STRATEGY: "Stratégie éditoriale",
}

// ─── Accompagnement récurrent ─────────────────────────────────────────────────

export const RECURRING_ITEM_LABELS: Record<string, string> = {
  RECURRING_ESSENTIAL_MAINTENANCE: "Maintenance technique",
  RECURRING_MAINTENANCE_FOLLOWUP: "Maintenance & support réactif",
  RECURRING_ACCOMPANIMENT: "Maintenance & mises à jour de contenu",
  RECURRING_REGULAR_EVOLUTION: "Accompagnement évolutif complet",
  RECURRING_CONTINUOUS_SEO: "SEO continu",
}

// ─── Hypothèses de cadrage ────────────────────────────────────────────────────

export const ASSUMPTION_MESSAGES: Record<string, string> = {
  scope_to_confirm:
    "Le périmètre exact devra être précisé lors du cadrage.",
  external_integration_to_confirm:
    "Les intégrations avec des services externes devront être analysées ensemble.",
  advanced_scope_requires_confirmation:
    "Plusieurs fonctionnalités avancées sont prévues — le périmètre technique sera à détailler lors du cadrage.",
  redesign_existing_system_to_audit:
    "L'existant devra être audité avant validation définitive du budget.",
  content_scope_to_confirm:
    "Les besoins en contenus seront précisés lors du cadrage.",
  unknown_project_requires_human_scoping:
    "La combinaison de vos besoins appelle un échange avant toute estimation chiffrée.",
}

// ─── Types de projet recommandés ─────────────────────────────────────────────

export const RECOMMENDED_PROJECT_TYPE_LABELS: Record<string, string> = {
  vitrinesite: "Site vitrine / institutionnel",
  ecommerce: "Site e-commerce",
  businessapp: "Application web métier",
  refonte: "Refonte de l'existant",
  other: "Projet sur mesure",
  unknown: "Projet à définir",
}

// ─── Période récurrente ───────────────────────────────────────────────────────

export const PERIOD_LABELS: Record<string, string> = {
  month: "/ mois",
}

// ─── Fonctions d'accès ────────────────────────────────────────────────────────

export function getLineItemLabel(code: string): string {
  return LINE_ITEM_LABELS[code] ?? "Prestation complémentaire"
}

export function getRecurringItemLabel(code: string): string {
  return RECURRING_ITEM_LABELS[code] ?? "Accompagnement complémentaire"
}

export function getAssumptionMessage(code: string): string {
  return ASSUMPTION_MESSAGES[code] ?? "Un point devra être précisé lors du cadrage."
}

export function getRecommendedProjectTypeLabel(code: string): string {
  return RECOMMENDED_PROJECT_TYPE_LABELS[code] ?? "Projet digital"
}

export function getPeriodLabel(period: string): string {
  return PERIOD_LABELS[period] ?? `/ ${period}`
}
