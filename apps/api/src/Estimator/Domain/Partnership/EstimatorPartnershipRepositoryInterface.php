<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Partnership;

use Symfony\Component\Uid\Uuid;

interface EstimatorPartnershipRepositoryInterface
{
    public function save(EstimatorPartnershipProposal $proposal): void;

    /**
     * Returns all proposals sorted by createdAt DESC.
     * @return list<EstimatorPartnershipProposal>
     */
    public function listAll(): array;

    public function findById(Uuid $id): ?EstimatorPartnershipProposal;
}
