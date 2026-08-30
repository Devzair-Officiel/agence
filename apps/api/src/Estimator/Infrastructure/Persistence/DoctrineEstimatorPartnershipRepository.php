<?php

declare(strict_types=1);

namespace App\Estimator\Infrastructure\Persistence;

use App\Estimator\Domain\Partnership\EstimatorPartnershipProposal;
use App\Estimator\Domain\Partnership\EstimatorPartnershipRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

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
}
