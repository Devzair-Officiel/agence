<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Application\Command;

use App\EditorialMedia\Application\Command\DeleteEditorialMedia;
use App\EditorialMedia\Application\Command\DeleteEditorialMediaHandler;
use App\EditorialMedia\Application\Exception\MediaInUseException;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaVariantStorageInterface;
use App\EditorialMedia\Domain\Exception\MediaAssetNotFoundException;
use App\EditorialMedia\Domain\ImageVariant;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\MediaVariantRecord;
use App\EditorialMedia\Domain\Sha256;
use App\EditorialMedia\Domain\VariantStorageKey;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\EditorialMedia\Support\FakeMediaStorage;
use App\Tests\EditorialMedia\Support\InMemoryMediaAssetRepository;
use App\Tests\EditorialMedia\Support\MediaAssetBuilder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Tests unitaires de DeleteEditorialMediaHandler (Phase 9D).
 *
 * Scénarios couverts :
 *   A. Suppression nominale d'un asset sans variants.
 *   B. Suppression nominale d'un asset avec card + hero.
 *   C. Asset introuvable → MediaAssetNotFoundException.
 *   D. Asset référencé → MediaInUseException, aucun fichier supprimé.
 *   E. Échec stockage original → MediaStorageException propagée.
 *   F. Échec variant card → MediaStorageException propagée.
 *   G. Échec variant hero (card OK) → MediaStorageException propagée.
 *   H. Flush échoue après suppression fichiers → exception propagée.
 *   I. Valeur de retour = MIME type de l'asset supprimé.
 */
final class DeleteEditorialMediaHandlerTest extends TestCase
{
    use EntityManagerStub;

    // ── A. Nominal sans variants ──────────────────────────────────────────────

    public function testDeletesOrphanAssetWithoutVariants(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $em      = $this->entityManagerExpectingFlush();

        $asset = (new MediaAssetBuilder())->build();
        $repo->save($asset);

        $handler = new DeleteEditorialMediaHandler($repo, $storage, $storage, $em);
        $handler(new DeleteEditorialMedia($asset->id()));

        self::assertSame(1, $storage->deleteCallCount, 'Fichier original supprimé.');
        self::assertSame(0, $storage->deleteVariantCallCount, 'Aucun variant présent.');
        self::assertSame(0, $repo->count(), 'Asset retiré du repository.');
    }

    // ── B. Nominal avec variants ──────────────────────────────────────────────

    public function testDeletesOrphanAssetWithVariants(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $em      = $this->entityManagerExpectingFlush();

        $asset = (new MediaAssetBuilder())->build();
        $id    = $asset->id();
        $asset->attachVariants(
            new MediaVariantRecord(VariantStorageKey::forVariant($id, ImageVariant::Card), 768, 432, 51200, Sha256::fromString(str_repeat('b', 64))),
            new MediaVariantRecord(VariantStorageKey::forVariant($id, ImageVariant::Hero), 1600, 900, 204800, Sha256::fromString(str_repeat('c', 64))),
        );
        $repo->save($asset);

        $handler = new DeleteEditorialMediaHandler($repo, $storage, $storage, $em);
        $handler(new DeleteEditorialMedia($id));

        self::assertSame(1, $storage->deleteCallCount, 'Fichier original supprimé.');
        self::assertSame(2, $storage->deleteVariantCallCount, 'Card et hero supprimés.');
        self::assertSame(0, $repo->count());
    }

    // ── C. Asset introuvable ──────────────────────────────────────────────────

    public function testThrowsWhenAssetNotFound(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $em      = $this->entityManagerExpectingNoFlush();

        $handler = new DeleteEditorialMediaHandler($repo, $storage, $storage, $em);

        $this->expectException(MediaAssetNotFoundException::class);
        $handler(new DeleteEditorialMedia(Uuid::v7()));
    }

    // ── D. Asset référencé ────────────────────────────────────────────────────

    public function testThrowsWhenMediaIsReferencedByArticle(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $em      = $this->entityManagerExpectingNoFlush();

        $asset = (new MediaAssetBuilder())->build();
        $repo->save($asset);
        $repo->markAsReferenced($asset->id());

        $handler = new DeleteEditorialMediaHandler($repo, $storage, $storage, $em);

        try {
            $handler(new DeleteEditorialMedia($asset->id()));
            self::fail('MediaInUseException attendue.');
        } catch (MediaInUseException) {
        }

        self::assertSame(0, $storage->deleteCallCount, 'Aucun fichier supprimé pour un asset référencé.');
        self::assertSame(1, $repo->count(), 'Asset référencé toujours présent.');
    }

    // ── E. Échec suppression fichier original ─────────────────────────────────

    public function testStorageFailureOnOriginalPropagates(): void
    {
        $repo = new InMemoryMediaAssetRepository();
        // deleteFails = true : delete() lèvera MediaStorageException.
        // L'asset n'a pas de variants, donc deleteVariant n'est jamais appelé.
        $storage = new FakeMediaStorage(deleteFails: true);
        $em      = $this->entityManagerExpectingNoFlush();

        $asset = (new MediaAssetBuilder())->build();
        $repo->save($asset);

        $handler = new DeleteEditorialMediaHandler($repo, $storage, $storage, $em);

        $this->expectException(MediaStorageException::class);
        $handler(new DeleteEditorialMedia($asset->id()));
    }

    // ── F. Échec suppression variant card ─────────────────────────────────────

    public function testStorageFailureOnCardVariantPropagates(): void
    {
        $repo = new InMemoryMediaAssetRepository();
        // deleteFails = true : deleteVariant(card) est le premier appel → lève.
        $storage = new FakeMediaStorage(deleteFails: true);
        $em      = $this->entityManagerExpectingNoFlush();

        $asset = (new MediaAssetBuilder())->build();
        $id    = $asset->id();
        $asset->attachVariants(
            new MediaVariantRecord(VariantStorageKey::forVariant($id, ImageVariant::Card), 768, 432, 51200, Sha256::fromString(str_repeat('b', 64))),
            new MediaVariantRecord(VariantStorageKey::forVariant($id, ImageVariant::Hero), 1600, 900, 204800, Sha256::fromString(str_repeat('c', 64))),
        );
        $repo->save($asset);

        $handler = new DeleteEditorialMediaHandler($repo, $storage, $storage, $em);

        try {
            $handler(new DeleteEditorialMedia($id));
            self::fail('MediaStorageException attendue.');
        } catch (MediaStorageException) {
        }

        self::assertSame(1, $repo->count(), 'Asset toujours en DB après échec storage card.');
    }

    // ── G. Échec suppression variant hero (card réussit) ─────────────────────

    public function testStorageFailureOnHeroVariantPropagates(): void
    {
        $repo = new InMemoryMediaAssetRepository();
        $em   = $this->entityManagerExpectingNoFlush();

        $asset   = (new MediaAssetBuilder())->build();
        $id      = $asset->id();
        $heroKey = VariantStorageKey::forVariant($id, ImageVariant::Hero);
        $asset->attachVariants(
            new MediaVariantRecord(VariantStorageKey::forVariant($id, ImageVariant::Card), 768, 432, 51200, Sha256::fromString(str_repeat('b', 64))),
            new MediaVariantRecord($heroKey, 1600, 900, 204800, Sha256::fromString(str_repeat('c', 64))),
        );
        $repo->save($asset);

        // Card réussit, hero lève MediaStorageException.
        $variantStorage = $this->createMock(MediaVariantStorageInterface::class);
        $variantStorage->method('deleteVariant')
            ->willReturnCallback(static function (VariantStorageKey $key) use ($heroKey): void {
                if ($key->toString() === $heroKey->toString()) {
                    throw MediaStorageException::writeFailed();
                }
            });

        $originalStorage = new FakeMediaStorage();

        $handler = new DeleteEditorialMediaHandler($repo, $originalStorage, $variantStorage, $em);

        $this->expectException(MediaStorageException::class);
        $handler(new DeleteEditorialMedia($id));
    }

    // ── H. Flush échoue après suppression fichiers ─────────────────────────────

    public function testFlushFailurePropagatesAfterFileDeletion(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();

        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->expects(self::once())->method('flush')
            ->willThrowException(new \RuntimeException('DB indisponible'));

        $asset = (new MediaAssetBuilder())->build();
        $repo->save($asset);

        $handler = new DeleteEditorialMediaHandler($repo, $storage, $storage, $em);

        try {
            $handler(new DeleteEditorialMedia($asset->id()));
            self::fail('RuntimeException attendue.');
        } catch (\RuntimeException $e) {
            self::assertSame('DB indisponible', $e->getMessage());
        }

        // Le fichier a déjà été supprimé du stockage avant l'échec du flush.
        self::assertSame(1, $storage->deleteCallCount, 'Fichier supprimé avant le flush.');
    }

    // ── I. Valeur de retour = MIME type ────────────────────────────────────────

    public function testReturnsMimeTypeOfDeletedAsset(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $em      = $this->entityManagerExpectingFlush();

        $asset = (new MediaAssetBuilder())->withType(MediaType::Png)->build();
        $repo->save($asset);

        $handler = new DeleteEditorialMediaHandler($repo, $storage, $storage, $em);
        $mime    = $handler(new DeleteEditorialMedia($asset->id()));

        self::assertSame('image/png', $mime);
    }
}
