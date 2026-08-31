<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

use App\Estimator\Domain\Pricing\Exception\InvalidPricingTransitionException;
use App\Estimator\Domain\Pricing\PricingConfiguration;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Publie un brouillon tarifaire de manière atomique.
 *
 * Séquence :
 * 1. Valider l'exhaustivité de la configuration (bloquant).
 * 2. Récupérer l'éventuelle version actuellement publiée.
 * 3. Appliquer published → archived sur l'ancienne version.
 * 4. Appliquer draft → published sur le brouillon.
 * 5. Persister les deux mutations dans une transaction unique (publishAtomically).
 *
 * Fail closed : si aucune configuration publiée n'existe après la transaction,
 * l'ActivePricingCatalog lèvera une NoPricingConfigurationAvailableException.
 *
 * @throws \InvalidArgumentException                si l'ID est introuvable
 * @throws InvalidPricingTransitionException         si la configuration n'est pas un brouillon
 * @throws \App\Estimator\Application\Pricing\PricingValidationException si la grille est incomplète
 */
final class PublishPricingConfiguration
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
        private readonly PricingConfigurationValidator $validator,
    ) {}

    public function publish(Uuid $id, ?Uuid $publishedBy = null): PricingConfiguration
    {
        $draft = $this->repository->findById($id);

        if ($draft === null) {
            throw new \InvalidArgumentException(sprintf('Configuration tarifaire introuvable : %s.', $id));
        }

        $definition = PricingCatalogDefinition::fromArray(
            $draft->version(),
            $draft->currency(),
            $draft->configuration(),
        );

        $errors = $this->validator->validate($definition);

        if ($errors !== []) {
            throw PricingValidationException::invalidConfiguration($draft->version(), $errors);
        }

        $currentPublished = $this->repository->findPublished();

        // Mutations are intentionally deferred to the repository so publishAtomically
        // can interleave archive-flush / publish-flush, satisfying the partial unique index.
        $this->repository->publishAtomically($draft, $currentPublished, $publishedBy);

        return $draft;
    }
}
