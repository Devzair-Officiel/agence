<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Infrastructure\Persistence;

use App\EditorialMedia\Domain\Exception\MediaAssetNotFoundException;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;
use App\EditorialMedia\Domain\StorageKey;
use App\EditorialMedia\Infrastructure\Persistence\DoctrineMediaAssetRepository;
use App\Tests\EditorialMedia\Support\EditorialMediaDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Test d'intégration Doctrine — nécessite `devzair_test` accessible.
 *
 * Chaque test s'exécute dans une transaction rollbackée en teardown pour
 * garantir l'idempotence quel que soit l'ordre d'exécution PHPUnit 12.
 */
final class DoctrineMediaAssetRepositoryTest extends KernelTestCase
{
    use EditorialMediaDatabaseCleanup;

    private EntityManagerInterface $entityManager;
    private DoctrineMediaAssetRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(DoctrineMediaAssetRepository::class);

        $this->clearEditorialMediaTables($this->entityManager);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
        $this->entityManager->close();
    }

    public function testSaveAndFindByIdRoundTrip(): void
    {
        $asset = $this->makeAsset('2026-08-08T10:00:00+00:00', 'photo.jpg');

        $this->repository->save($asset);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $found = $this->repository->findById($asset->id());

        self::assertNotNull($found);
        self::assertSame($asset->id()->toRfc4122(), $found->id()->toRfc4122());
        self::assertSame('photo.jpg', $found->originalFilename());
        self::assertSame(MediaType::Jpeg, $found->mimeType());
        self::assertSame(1200, $found->width());
    }

    public function testGetByIdThrowsWhenMissing(): void
    {
        $this->expectException(MediaAssetNotFoundException::class);

        $this->repository->getById(Uuid::v7());
    }

    public function testListReturnsInsertsInCreatedAtDescOrder(): void
    {
        $ancien = $this->makeAsset('2026-08-01T10:00:00+00:00', 'ancien.jpg');
        $milieu = $this->makeAsset('2026-08-05T10:00:00+00:00', 'milieu.jpg');
        $recent = $this->makeAsset('2026-08-08T10:00:00+00:00', 'recent.jpg');

        // Insertion volontairement dans le désordre pour vérifier l'ORDER BY.
        $this->repository->save($milieu);
        $this->repository->save($recent);
        $this->repository->save($ancien);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $page = $this->repository->list(1, 20);

        self::assertCount(3, $page);
        self::assertSame('recent.jpg', $page[0]->originalFilename());
        self::assertSame('milieu.jpg', $page[1]->originalFilename());
        self::assertSame('ancien.jpg', $page[2]->originalFilename());
    }

    public function testCountMatchesInsertedRows(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $this->repository->save($this->makeAsset(
                \sprintf('2026-08-%02dT10:00:00+00:00', 1 + $i),
                \sprintf('m-%d.jpg', $i),
            ));
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        self::assertSame(3, $this->repository->count());
    }

    public function testListRespectsPagination(): void
    {
        for ($i = 0; $i < 5; ++$i) {
            $this->repository->save($this->makeAsset(
                \sprintf('2026-08-%02dT10:00:00+00:00', 1 + $i),
                \sprintf('m-%d.jpg', $i),
            ));
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        $firstPage = $this->repository->list(1, 2);
        $secondPage = $this->repository->list(2, 2);

        self::assertCount(2, $firstPage);
        self::assertCount(2, $secondPage);
        self::assertNotEquals(
            $firstPage[0]->id()->toRfc4122(),
            $secondPage[0]->id()->toRfc4122(),
        );
    }

    public function testSaveDoesNotFlushAutomatically(): void
    {
        $asset = $this->makeAsset('2026-08-08T10:00:00+00:00', 'no-flush.jpg');

        $this->repository->save($asset);
        // Pas de flush → count doit rester à 0.

        self::assertSame(0, $this->repository->count());
    }

    private function makeAsset(string $createdAt, string $filename): MediaAsset
    {
        $id = Uuid::v7();

        return MediaAsset::record(
            id: $id,
            storageKey: StorageKey::forNewAsset($id, MediaType::Jpeg),
            originalFilename: $filename,
            mimeType: MediaType::Jpeg,
            sizeBytes: 4096,
            dimensions: MediaDimensions::of(1200, 800),
            sha256: Sha256::fromString(str_repeat('a', 64)),
            now: new \DateTimeImmutable($createdAt),
        );
    }
}
