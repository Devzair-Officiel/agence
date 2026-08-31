<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

use App\Estimator\Domain\Pricing\PricingConfiguration;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Met à jour la configuration d'un brouillon tarifaire.
 *
 * La validation métier (exhaustivité de la grille) est exécutée en avertissement
 * uniquement lors de l'édition — la configuration incomplète est autorisée tant
 * qu'elle n'est pas publiée. La publication, elle, revalide et bloque.
 */
final class UpdatePricingDraft
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
    ) {}

    /**
     * @throws \App\Estimator\Domain\Pricing\Exception\PricingConfigurationImmutableException si non-draft
     * @return list<string> erreurs de validation (non bloquantes en édition)
     */
    public function update(Uuid $id, PricingCatalogDefinition $definition): array
    {
        $config = $this->repository->findById($id);

        if ($config === null) {
            throw new \InvalidArgumentException(sprintf('Configuration tarifaire introuvable : %s.', $id));
        }

        $config->updateConfiguration($definition->toArray());
        $this->repository->save($config);

        $validator = new PricingConfigurationValidator();

        return $validator->validate($definition);
    }
}
