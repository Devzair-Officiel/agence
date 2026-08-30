<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Lead;

interface EstimatorLeadRepositoryInterface
{
    public function save(EstimatorLead $lead): void;
}
