<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Pricing;

use Symfony\Component\Uid\Uuid;

interface PricingConfigurationRepositoryInterface
{
    public function save(PricingConfiguration $config): void;

    public function findById(Uuid $id): ?PricingConfiguration;

    public function findByVersion(string $version): ?PricingConfiguration;

    public function findPublished(): ?PricingConfiguration;

    /**
     * Returns all configurations sorted by createdAt DESC.
     *
     * @return list<PricingConfiguration>
     */
    public function listAll(): array;

    /**
     * Deletes a draft configuration. Only draft may be deleted.
     * The caller is responsible for verifying status before invoking.
     */
    public function deleteDraft(PricingConfiguration $config): void;

    /**
     * Atomically archives the current published configuration (if any) and
     * publishes the draft in a single DB transaction.
     *
     * The repository is responsible for calling archive() then publish() in
     * the correct order so the partial unique index (one published row) is
     * never violated. Callers must NOT pre-mutate the entities.
     */
    public function publishAtomically(PricingConfiguration $toPublish, ?PricingConfiguration $toArchive, ?Uuid $publishedBy = null): void;
}
