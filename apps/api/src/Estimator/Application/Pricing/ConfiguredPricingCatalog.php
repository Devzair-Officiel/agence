<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\PricingVersion;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;

/**
 * Implémentation runtime de PricingCatalogInterface alimentée par une
 * PricingCatalogDefinition chargée depuis la base de données.
 *
 * Ce catalogue est immuable : une fois construit avec une définition, il ne
 * chargera plus rien. Il est utilisé pour UNE requête HTTP uniquement.
 */
final class ConfiguredPricingCatalog implements PricingCatalogInterface
{
    public function __construct(
        private readonly PricingCatalogDefinition $definition,
    ) {}

    public function version(): PricingVersion
    {
        return PricingVersion::fromString($this->definition->version);
    }

    public function baseAmounts(ProjectType $type): ?array
    {
        return $this->definition->baseAmounts[$type->value] ?? null;
    }

    public function unknownFallbackAmounts(): array
    {
        return ['min' => 90_000, 'max' => 400_000];
    }

    public function scalePercents(ProjectScale $scale): array
    {
        $amounts = $this->definition->scalePercents[$scale->value] ?? null;

        if ($amounts === null) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Aucun scale_percent défini pour la taille "%s".', $scale->value)
            );
        }

        return $amounts;
    }

    public function featureComplexity(ProjectFeature $feature): FeatureComplexity
    {
        $value = $this->definition->featureComplexity[$feature->value] ?? null;

        if ($value === null) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Aucune complexité définie pour la fonctionnalité "%s".', $feature->value)
            );
        }

        $complexity = FeatureComplexity::tryFrom($value);

        if ($complexity === null) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Complexité inconnue "%s" pour la fonctionnalité "%s".', $value, $feature->value)
            );
        }

        return $complexity;
    }

    public function complexityAmounts(FeatureComplexity $complexity): array
    {
        $amounts = $this->definition->complexityAmounts[$complexity->value] ?? null;

        if ($amounts === null) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Aucun montant de complexité défini pour "%s".', $complexity->value)
            );
        }

        return $amounts;
    }

    public function contentAmounts(ContentNeed $need): array
    {
        $amounts = $this->definition->contentAmounts[$need->value] ?? null;

        if ($amounts === null) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Aucun montant défini pour le besoin contenu "%s".', $need->value)
            );
        }

        return $amounts;
    }

    public function visibilityAmounts(VisibilityNeed $need): ?array
    {
        if (!array_key_exists($need->value, $this->definition->visibilityAmounts)) {
            throw EstimatorInvariantViolation::invalidInput(
                sprintf('Aucun montant défini pour le besoin visibilité "%s".', $need->value)
            );
        }

        return $this->definition->visibilityAmounts[$need->value];
    }

    public function recurringAmounts(string $tier): array
    {
        $amounts = $this->definition->recurringAmounts[$tier] ?? null;

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
