<?php

declare(strict_types=1);

namespace App\Estimator\Infrastructure\Persistence;

use App\Estimator\Domain\Pricing\PricingConfiguration;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use App\Estimator\Domain\Pricing\PricingConfigurationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DoctrineEstimatorPricingRepository implements PricingConfigurationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function save(PricingConfiguration $config): void
    {
        $this->entityManager->persist($config);
        $this->entityManager->flush();
    }

    public function findById(Uuid $id): ?PricingConfiguration
    {
        return $this->entityManager->find(PricingConfiguration::class, $id);
    }

    public function findByVersion(string $version): ?PricingConfiguration
    {
        return $this->entityManager
            ->getRepository(PricingConfiguration::class)
            ->findOneBy(['version' => $version]);
    }

    public function findPublished(): ?PricingConfiguration
    {
        return $this->entityManager
            ->getRepository(PricingConfiguration::class)
            ->findOneBy(['status' => PricingConfigurationStatus::Published]);
    }

    public function listAll(): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT c FROM App\Estimator\Domain\Pricing\PricingConfiguration c ORDER BY c.createdAt DESC'
            )
            ->getResult();
    }

    public function deleteDraft(PricingConfiguration $config): void
    {
        $this->entityManager->remove($config);
        $this->entityManager->flush();
    }

    public function publishAtomically(PricingConfiguration $toPublish, ?PricingConfiguration $toArchive, ?Uuid $publishedBy = null): void
    {
        $this->entityManager->wrapInTransaction(function () use ($toPublish, $toArchive, $publishedBy): void {
            // Mutations happen here — NOT before the call — so each flush() only
            // sees one dirty entity, guaranteeing the SQL ordering required by the
            // partial unique index (uniq_pricing_one_published):
            // 1. Archive old → 0 published rows in DB.
            // 2. Publish new → 1 published row in DB.
            if ($toArchive !== null) {
                $toArchive->archive();
                $this->entityManager->persist($toArchive);
                $this->entityManager->flush();
            }
            $toPublish->publish($publishedBy);
            $this->entityManager->persist($toPublish);
            $this->entityManager->flush();
        });
    }
}
