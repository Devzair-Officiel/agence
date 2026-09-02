<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Lead;

use Symfony\Component\Uid\Uuid;

interface EstimatorLeadRepositoryInterface
{
    public function save(EstimatorLead $lead): void;

    /**
     * Returns all leads sorted by createdAt DESC.
     * @return list<EstimatorLead>
     */
    public function listAll(): array;

    public function findById(Uuid $id): ?EstimatorLead;

    /** Returns the number of leads with createdAt strictly before $cutoff (dry-run support). */
    public function countExpiredBefore(\DateTimeImmutable $cutoff): int;

    /**
     * Bulk-deletes leads with createdAt strictly before $cutoff.
     * Returns the number of rows deleted.
     */
    public function deleteExpiredBefore(\DateTimeImmutable $cutoff): int;
}
