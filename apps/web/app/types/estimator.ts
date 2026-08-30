/**
 * Codes backend exacts — alignés sur les enums Symfony Estimator Domain.
 *
 * Aucune valeur UI française ici : ce sont les valeurs transmises à POST /api/estimate.
 * Tout changement côté Symfony doit être reflété ici.
 */

// ProjectType — apps/api/src/Estimator/Domain/ProjectType.php
export type ProjectTypeCode =
  | "vitrinesite"
  | "ecommerce"
  | "businessapp"
  | "refonte"
  | "other"
  | "unknown"

// ProjectObjective — apps/api/src/Estimator/Domain/ProjectObjective.php
export type ProjectObjectiveCode =
  | "present_business"
  | "sell_online"
  | "generate_leads"
  | "digitalize_process"
  | "improve_existing"
  | "improve_visibility"

// CurrentSituation — apps/api/src/Estimator/Domain/CurrentSituation.php
export type CurrentSituationCode =
  | "none"
  | "has_website"
  | "has_ecommerce"
  | "has_business_app"
  | "unknown"

// ProjectScale — apps/api/src/Estimator/Domain/ProjectScale.php
export type ProjectScaleCode = "small" | "medium" | "large" | "unknown"

// ProjectFeature — apps/api/src/Estimator/Domain/ProjectFeature.php
export type ProjectFeatureCode =
  | "contact_form"
  | "booking_form"
  | "multilingual"
  | "payment"
  | "shipping"
  | "customer_accounts"
  | "authentication"
  | "roles_and_permissions"
  | "content_management"
  | "document_generation"
  | "notifications"
  | "import_export"
  | "api_integration"
  | "stock_management"
  | "promotions"

// ContentNeed — apps/api/src/Estimator/Domain/ContentNeed.php
export type ContentNeedCode =
  | "visual_identity_needed"
  | "visual_identity_partial"
  | "content_to_write"
  | "content_with_help"
  | "photography_needed"

// VisibilityNeed — apps/api/src/Estimator/Domain/VisibilityNeed.php
export type VisibilityNeedCode =
  | "seo_basic"
  | "seo_advanced"
  | "local_visibility"
  | "editorial_strategy"

// CareNeed — apps/api/src/Estimator/Domain/CareNeed.php
export type CareNeedCode =
  | "maintenance"
  | "content_updates"
  | "continuous_seo"
  | "support"
  | "functional_evolution"

// Payload compatible POST /api/estimate (EstimateRequest DTO Symfony)
export interface EstimatePayload {
  project_type: ProjectTypeCode
  objectives: ProjectObjectiveCode[]
  current_situation: CurrentSituationCode
  scale: ProjectScaleCode
  features: ProjectFeatureCode[]
  content_needs: ContentNeedCode[]
  visibility_needs: VisibilityNeedCode[]
  care_needs: CareNeedCode[]
}
