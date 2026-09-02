<?php

declare(strict_types=1);

namespace App\Estimator\Infrastructure\Persistence;

use App\Estimator\Domain\Partnership\EstimatorPartnershipProposal;
use App\Estimator\Domain\Partnership\EstimatorPartnershipRepositoryInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DoctrineEstimatorPartnershipRepository implements EstimatorPartnershipRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function save(EstimatorPartnershipProposal $proposal): void
    {
        $this->em->persist($proposal);
        $this->em->flush();
    }

    public function listAll(): array
    {
        return $this->em
            ->createQuery(
                'SELECT p FROM App\Estimator\Domain\Partnership\EstimatorPartnershipProposal p ORDER BY p.createdAt DESC'
            )
            ->getResult();
    }

    public function findById(Uuid $id): ?EstimatorPartnershipProposal
    {
        return $this->em->find(EstimatorPartnershipProposal::class, $id);
    }

    public function countExpiredBefore(\DateTimeImmutable $cutoff): int
    {
        return (int) $this->em
            ->createQuery(
                'SELECT COUNT(p.id) FROM App\Estimator\Domain\Partnership\EstimatorPartnershipProposal p WHERE p.createdAt < :cutoff'
            )
            ->setParameter('cutoff', $cutoff, Types::DATETIMETZ_IMMUTABLE)
            ->getSingleScalarResult();
    }

    public function deleteExpiredBefore(\DateTimeImmutable $cutoff): int
    {
        return (int) $this->em
            ->createQuery(
                'DELETE FROM App\Estimator\Domain\Partnership\EstimatorPartnershipProposal p WHERE p.createdAt < :cutoff'
            )
            ->setParameter('cutoff', $cutoff, Types::DATETIMETZ_IMMUTABLE)
            ->execute();
    }
}
