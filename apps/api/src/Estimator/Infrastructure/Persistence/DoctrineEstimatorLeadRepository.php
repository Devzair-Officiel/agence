<?php

declare(strict_types=1);

namespace App\Estimator\Infrastructure\Persistence;

use App\Estimator\Domain\Lead\EstimatorLead;
use App\Estimator\Domain\Lead\EstimatorLeadRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineEstimatorLeadRepository implements EstimatorLeadRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function save(EstimatorLead $lead): void
    {
        $this->entityManager->persist($lead);
        $this->entityManager->flush();
    }
}
