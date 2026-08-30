<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Partnership;

interface EstimatorPartnershipRepositoryInterface
{
    public function save(EstimatorPartnershipProposal $proposal): void;
}
