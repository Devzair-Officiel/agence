<?php

declare(strict_types=1);

namespace App\Estimator\Infrastructure\Persistence;

use App\Estimator\Domain\Lead\EstimatorLead;
use App\Estimator\Domain\Lead\EstimatorLeadRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

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

    public function listAll(): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT l FROM App\Estimator\Domain\Lead\EstimatorLead l ORDER BY l.createdAt DESC'
            )
            ->getResult();
    }

    public function findById(Uuid $id): ?EstimatorLead
    {
        return $this->entityManager->find(EstimatorLead::class, $id);
    }
}
