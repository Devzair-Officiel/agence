<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

use App\Estimator\Domain\Pricing\Exception\DuplicatePricingVersionException;
use App\Estimator\Domain\Pricing\PricingConfiguration;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Crée un brouillon de configuration tarifaire en clonant la version publiée.
 *
 * L'administrateur reçoit une grille complète à modifier — jamais un brouillon
 * vide nécessitant de ressaisir toutes les valeurs.
 */
final class CreatePricingDraft
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
    ) {}

    /**
     * @throws DuplicatePricingVersionException si la version label est déjà prise
     */
    public function create(string $newVersion, string $currency, PricingCatalogDefinition $definition, ?Uuid $createdBy = null): PricingConfiguration
    {
        $trimmedVersion = trim($newVersion);

        if ($trimmedVersion === '') {
            throw new \InvalidArgumentException('Le label de version ne peut pas être vide.');
        }

        if ($this->repository->findByVersion($trimmedVersion) !== null) {
            throw DuplicatePricingVersionException::versionAlreadyExists($trimmedVersion);
        }

        $config = PricingConfiguration::create(
            id:            Uuid::v7(),
            version:       $trimmedVersion,
            currency:      $currency,
            configuration: $definition->toArray(),
            createdBy:     $createdBy,
        );

        $this->repository->save($config);

        return $config;
    }
}
