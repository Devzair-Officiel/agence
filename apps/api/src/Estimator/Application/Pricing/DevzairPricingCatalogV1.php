<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\PricingVersion;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;

/**
 * Grille tarifaire Devzair V1 — 2026-v1.
 *
 * Cette classe contient la POLITIQUE COMMERCIALE versionnée de Devzair.
 * Ce ne sont PAS des fixtures de test.
 *
 * Stratégie tarifaire :
 * - prix d'entrée accessibles, sans positionnement low-cost ;
 * - valeur du travail préservée ;
 * - forte orientation vers les prestations récurrentes ;
 * - investissement initial et récurrent strictement séparés.
 *
 * Tous les montants sont en centimes EUR (int).
 * Aucune opération flottante dans le domaine.
 *
 * DETTE TECHNIQUE :
 * - ContentNeed ne couvre pas "OFFER / CONTENT STRUCTURING" (200–500 €).
 *   À modéliser dans un enum futur ou un ContentNeed dédié.
 * - L'hébergement n'est pas distingué dans CareNeed V1.
 *   Un enum futur ou une option de formule le représentera en EST-8.
 */
final class DevzairPricingCatalogV1 implements PricingCatalogInterface
{
    private const VERSION = '2026-v1';

    /** @var array<string, array{min: int, max: int}> */
    private const BASE_AMOUNTS = [
        'vitrinesite' => ['min' => 90_000, 'max' => 130_000],
        'ecommerce'   => ['min' => 170_000, 'max' => 250_000],
        'businessapp' => ['min' => 250_000, 'max' => 400_000],
        'refonte'     => ['min' => 70_000,  'max' => 130_000],
    ];

    /**
     * Scale percentage basis (100 = no change).
     * Applied only to the base socle, not to feature/content/visibility additions.
     *
     * @var array<string, array{min: int, max: int}>
     */
    private const SCALE_PERCENTS = [
        'small'   => ['min' => 100, 'max' => 100],
        'medium'  => ['min' => 115, 'max' => 120],
        'large'   => ['min' => 130, 'max' => 145],
        'unknown' => ['min' => 100, 'max' => 100],
    ];

    /** @var array<string, FeatureComplexity> */
    private const FEATURE_COMPLEXITY = [
        'contact_form'         => FeatureComplexity::Light,
        'multilingual'         => FeatureComplexity::Light,
        'content_management'   => FeatureComplexity::Light,
        'booking_form'         => FeatureComplexity::Medium,
        'payment'              => FeatureComplexity::Medium,
        'shipping'             => FeatureComplexity::Medium,
        'customer_accounts'    => FeatureComplexity::Medium,
        'notifications'        => FeatureComplexity::Medium,
        'import_export'        => FeatureComplexity::Medium,
        'stock_management'     => FeatureComplexity::Medium,
        'promotions'           => FeatureComplexity::Medium,
        'authentication'       => FeatureComplexity::Advanced,
        'roles_and_permissions' => FeatureComplexity::Advanced,
        'document_generation'  => FeatureComplexity::Advanced,
        'api_integration'      => FeatureComplexity::Advanced,
    ];

    /** @var array<string, array{min: int, max: int}> */
    private const COMPLEXITY_AMOUNTS = [
        'light'    => ['min' => 10_000,  'max' => 35_000],
        'medium'   => ['min' => 35_000,  'max' => 80_000],
        'advanced' => ['min' => 70_000,  'max' => 180_000],
    ];

    /** @var array<string, array{min: int, max: int}> */
    private const CONTENT_AMOUNTS = [
        'visual_identity_needed'  => ['min' => 50_000, 'max' => 100_000],
        'visual_identity_partial' => ['min' => 25_000, 'max' => 50_000],
        'content_to_write'        => ['min' => 40_000, 'max' => 90_000],
        'content_with_help'       => ['min' => 15_000, 'max' => 40_000],
        'photography_needed'      => ['min' => 25_000, 'max' => 60_000],
    ];

    /**
     * One-off visibility amounts. null = included in all project bases.
     * SEO de base est inclus dans tous les socles — jamais facturé séparément.
     *
     * @var array<string, array{min: int, max: int}|null>
     */
    private const VISIBILITY_AMOUNTS = [
        'seo_basic'          => null,
        'seo_advanced'       => ['min' => 30_000, 'max' => 80_000],
        'local_visibility'   => ['min' => 20_000, 'max' => 50_000],
        'editorial_strategy' => ['min' => 30_000, 'max' => 70_000],
    ];

    /**
     * Monthly recurring amounts in cents.
     * Anti-double-billing: the engine selects one base maintenance tier.
     *
     * ESSENTIAL_MAINTENANCE : maintenance corrective basique.
     * MAINTENANCE_FOLLOWUP  : maintenance + suivi réactif (couvre Maintenance + Support).
     * ACCOMPANIMENT         : maintenance + mises à jour éditoriales + suivi.
     * REGULAR_EVOLUTION     : accompagnement complet + évolutions fonctionnelles.
     * CONTINUOUS_SEO        : SEO continu, orthogonal aux tiers maintenance.
     *
     * @var array<string, array{min: int, max: int}>
     */
    private const RECURRING_AMOUNTS = [
        'ESSENTIAL_MAINTENANCE' => ['min' => 4_900,  'max' => 6_900],
        'MAINTENANCE_FOLLOWUP'  => ['min' => 8_900,  'max' => 12_900],
        'ACCOMPANIMENT'         => ['min' => 14_900, 'max' => 21_900],
        'REGULAR_EVOLUTION'     => ['min' => 24_900, 'max' => 39_900],
        'CONTINUOUS_SEO'        => ['min' => 30_000, 'max' => 60_000],
    ];

    public function version(): PricingVersion
    {
        return PricingVersion::fromString(self::VERSION);
    }

    public function baseAmounts(ProjectType $type): ?array
    {
        return self::BASE_AMOUNTS[$type->value] ?? null;
    }

    public function unknownFallbackAmounts(): array
    {
        return ['min' => 90_000, 'max' => 400_000];
    }

    public function scalePercents(ProjectScale $scale): array
    {
        return self::SCALE_PERCENTS[$scale->value];
    }

    public function featureComplexity(ProjectFeature $feature): FeatureComplexity
    {
        $complexity = self::FEATURE_COMPLEXITY[$feature->value] ?? null;

        if ($complexity === null) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Aucune complexité définie pour la fonctionnalité "%s".', $feature->value)
            );
        }

        return $complexity;
    }

    public function complexityAmounts(FeatureComplexity $complexity): array
    {
        return self::COMPLEXITY_AMOUNTS[$complexity->value];
    }

    public function contentAmounts(ContentNeed $need): array
    {
        $amounts = self::CONTENT_AMOUNTS[$need->value] ?? null;

        if ($amounts === null) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Aucun montant défini pour le besoin contenu "%s".', $need->value)
            );
        }

        return $amounts;
    }

    public function visibilityAmounts(VisibilityNeed $need): ?array
    {
        if (!array_key_exists($need->value, self::VISIBILITY_AMOUNTS)) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Aucun montant défini pour le besoin visibilité "%s".', $need->value)
            );
        }

        return self::VISIBILITY_AMOUNTS[$need->value];
    }

    public function recurringAmounts(string $tier): array
    {
        $amounts = self::RECURRING_AMOUNTS[$tier] ?? null;

        if ($amounts === null) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Aucun montant récurrent défini pour le tier "%s".', $tier)
            );
        }

        return $amounts;
    }

    public function advancedFeaturesWarningThreshold(): int
    {
        return 2;
    }
}
