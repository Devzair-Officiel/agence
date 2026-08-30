<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\PricingVersion;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;

interface PricingCatalogInterface
{
    public function version(): PricingVersion;

    /**
     * Base project amounts in cents.
     * Returns null for ProjectType::Other and ProjectType::Unknown (no automatic base).
     *
     * @return array{min: int, max: int}|null
     */
    public function baseAmounts(ProjectType $type): ?array;

    /**
     * Wide bracket for projects where type cannot be determined automatically.
     * Used when ProjectType::Unknown/Other cannot be inferred from objectives.
     *
     * @return array{min: int, max: int}
     */
    public function unknownFallbackAmounts(): array;

    /**
     * Scale percentage basis applied to the base socle only.
     * 100 = no change, 115 = +15%, 130 = +30%.
     *
     * @return array{min: int, max: int}
     */
    public function scalePercents(ProjectScale $scale): array;

    public function featureComplexity(ProjectFeature $feature): FeatureComplexity;

    /**
     * One-off amounts in cents for the given feature complexity class.
     *
     * @return array{min: int, max: int}
     */
    public function complexityAmounts(FeatureComplexity $complexity): array;

    /**
     * One-off content amounts in cents.
     * Returns null if the need has no pricing impact (hypothetical future case).
     *
     * @return array{min: int, max: int}
     */
    public function contentAmounts(ContentNeed $need): array;

    /**
     * One-off visibility amounts in cents.
     * Returns null when the visibility level is included in all project bases (SeoBasic).
     *
     * @return array{min: int, max: int}|null
     */
    public function visibilityAmounts(VisibilityNeed $need): ?array;

    /**
     * Monthly recurring amounts in cents for the given tier code.
     * Valid tier codes: ESSENTIAL_MAINTENANCE, MAINTENANCE_FOLLOWUP, ACCOMPANIMENT,
     * REGULAR_EVOLUTION, CONTINUOUS_SEO.
     *
     * @return array{min: int, max: int}
     */
    public function recurringAmounts(string $tier): array;

    /**
     * Number of ADVANCED-class features at which the engine adds
     * the 'advanced_scope_requires_confirmation' assumption.
     */
    public function advancedFeaturesWarningThreshold(): int;
}
