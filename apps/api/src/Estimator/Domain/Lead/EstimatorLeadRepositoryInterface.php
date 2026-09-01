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
}
