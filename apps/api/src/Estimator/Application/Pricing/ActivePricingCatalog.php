<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\Pricing\Exception\NoPricingConfigurationAvailableException;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use App\Estimator\Domain\PricingVersion;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;

/**
 * Implémentation de PricingCatalogInterface qui charge la configuration
 * publiée depuis la base de données.
 *
 * Chargement lazy : la requête DB n'est effectuée qu'à la première utilisation,
 * une seule fois par requête HTTP (service Symfony partagé = singleton par
 * requête). Ainsi les routes admin qui ne font pas d'estimation n'interrogent
 * pas la table pricing.
 *
 * Fail closed : si aucune configuration publiée n'existe, une exception est
 * levée — jamais de retour silencieux vers la grille hardcodée V1.
 */
final class ActivePricingCatalog implements PricingCatalogInterface
{
    private ?PricingCatalogInterface $inner = null;

    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
    ) {}

    public function version(): PricingVersion
    {
        return $this->catalog()->version();
    }

    public function baseAmounts(ProjectType $type): ?array
    {
        return $this->catalog()->baseAmounts($type);
    }

    public function unknownFallbackAmounts(): array
    {
        return $this->catalog()->unknownFallbackAmounts();
    }

    public function scalePercents(ProjectScale $scale): array
    {
        return $this->catalog()->scalePercents($scale);
    }

    public function featureComplexity(ProjectFeature $feature): FeatureComplexity
    {
        return $this->catalog()->featureComplexity($feature);
    }

    public function complexityAmounts(FeatureComplexity $complexity): array
    {
        return $this->catalog()->complexityAmounts($complexity);
    }

    public function contentAmounts(ContentNeed $need): array
    {
        return $this->catalog()->contentAmounts($need);
    }

    public function visibilityAmounts(VisibilityNeed $need): ?array
    {
        return $this->catalog()->visibilityAmounts($need);
    }

    public function recurringAmounts(string $tier): array
    {
        return $this->catalog()->recurringAmounts($tier);
    }

    public function advancedFeaturesWarningThreshold(): int
    {
        return $this->catalog()->advancedFeaturesWarningThreshold();
    }

    private function catalog(): PricingCatalogInterface
    {
        if ($this->inner !== null) {
            return $this->inner;
        }

        $config = $this->repository->findPublished();

        if ($config === null) {
            throw NoPricingConfigurationAvailableException::noPublishedConfiguration();
        }

        $definition    = PricingCatalogDefinition::fromArray($config->version(), $config->currency(), $config->configuration());
        $this->inner   = new ConfiguredPricingCatalog($definition);

        return $this->inner;
    }
}
