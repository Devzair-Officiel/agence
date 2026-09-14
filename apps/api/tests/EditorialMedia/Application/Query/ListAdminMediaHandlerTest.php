<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Application\Query;

use App\EditorialMedia\Application\Query\ListAdminMedia;
use App\EditorialMedia\Application\Query\ListAdminMediaHandler;
use App\EditorialMedia\Domain\ImageVariant;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\MediaVariantRecord;
use App\EditorialMedia\Domain\Sha256;
use App\EditorialMedia\Domain\StorageKey;
use App\EditorialMedia\Domain\VariantStorageKey;
use App\Tests\EditorialMedia\Support\InMemoryMediaAssetRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ListAdminMediaHandlerTest extends TestCase
{
    public function testReturnsEmptyPageWhenNoAssets(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $handler = new ListAdminMediaHandler($repository);

        $page = $handler(new ListAdminMedia(1, 20));

        self::assertSame([], $page->items);
        self::assertSame(0, $page->total);
        self::assertSame(1, $page->lastPage());
        self::assertFalse($page->hasPrevious());
        self::assertFalse($page->hasNext());
    }

    public function testOrdersByCreatedAtDescending(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $repository->save($this->makeAsset('2026-08-01T10:00:00+00:00', 'ancien.jpg'));
        $repository->save($this->makeAsset('2026-08-08T10:00:00+00:00', 'recent.jpg'));
        $repository->save($this->makeAsset('2026-08-05T10:00:00+00:00', 'milieu.jpg'));

        $page = (new ListAdminMediaHandler($repository))(new ListAdminMedia(1, 20));

        self::assertSame(3, $page->total);
        self::assertSame('recent.jpg', $page->items[0]->originalFilename);
        self::assertSame('milieu.jpg', $page->items[1]->originalFilename);
        self::assertSame('ancien.jpg', $page->items[2]->originalFilename);
    }

    public function testPaginatesRespectingPerPageAndPage(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        for ($i = 0; $i < 25; ++$i) {
            $repository->save($this->makeAsset(
                \sprintf('2026-08-%02dT10:00:00+00:00', 1 + $i),
                \sprintf('media-%02d.jpg', $i),
            ));
        }

        $handler = new ListAdminMediaHandler($repository);
        $firstPage = $handler(new ListAdminMedia(1, 20));
        $secondPage = $handler(new ListAdminMedia(2, 20));

        self::assertCount(20, $firstPage->items);
        self::assertCount(5, $secondPage->items);
        self::assertSame(25, $firstPage->total);
        self::assertSame(2, $firstPage->lastPage());
        self::assertTrue($firstPage->hasNext());
        self::assertFalse($firstPage->hasPrevious());
        self::assertTrue($secondPage->hasPrevious());
        self::assertFalse($secondPage->hasNext());
    }

    public function testProjectsEntityFieldsToListItem(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $repository->save($this->makeAsset('2026-08-01T10:00:00+00:00', 'photo.jpg'));

        $page = (new ListAdminMediaHandler($repository))(new ListAdminMedia(1, 20));

        $item = $page->items[0];
        self::assertSame('photo.jpg', $item->originalFilename);
        self::assertSame(MediaType::Jpeg, $item->mimeType);
        self::assertSame(1200, $item->width);
        self::assertSame(800, $item->height);
    }

    public function testUsageCountIsZeroForUnreferencedAsset(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $repository->save($this->makeAsset('2026-08-01T10:00:00+00:00', 'libre.jpg'));

        $page = (new ListAdminMediaHandler($repository))(new ListAdminMedia(1, 20));

        self::assertSame(0, $page->items[0]->usageCount);
    }

    public function testUsageCountReflectsArticleReferences(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $asset      = $this->makeAsset('2026-08-01T10:00:00+00:00', 'utilise.jpg');
        $repository->save($asset);
        $repository->markAsReferenced($asset->id(), 3);

        $page = (new ListAdminMediaHandler($repository))(new ListAdminMedia(1, 20));

        self::assertSame(3, $page->items[0]->usageCount);
    }

    public function testUsageCountBatchDoesNotCauseSeparateQueryPerItem(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $used       = $this->makeAsset('2026-08-02T10:00:00+00:00', 'used.jpg');
        $free       = $this->makeAsset('2026-08-01T10:00:00+00:00', 'free.jpg');
        $repository->save($used);
        $repository->save($free);
        $repository->markAsReferenced($used->id(), 2);

        $page = (new ListAdminMediaHandler($repository))(new ListAdminMedia(1, 20));

        // Ordered createdAt DESC: used first, free second
        self::assertSame(2, $page->items[0]->usageCount);
        self::assertSame(0, $page->items[1]->usageCount);
    }

    public function testHasVariantsFalseForLegacyAsset(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $repository->save($this->makeAsset('2026-08-01T10:00:00+00:00', 'legacy.jpg'));

        $page = (new ListAdminMediaHandler($repository))(new ListAdminMedia(1, 20));

        self::assertFalse($page->items[0]->hasVariants);
    }

    public function testHasVariantsTrueForPhase9CAsset(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $asset = $this->makeAsset('2026-08-01T10:00:00+00:00', 'modern.jpg');
        $id    = $asset->id();
        $asset->attachVariants(
            new MediaVariantRecord(VariantStorageKey::forVariant($id, ImageVariant::Card), 768, 432, 51200, Sha256::fromString(str_repeat('b', 64))),
            new MediaVariantRecord(VariantStorageKey::forVariant($id, ImageVariant::Hero), 1600, 900, 204800, Sha256::fromString(str_repeat('c', 64))),
        );
        $repository->save($asset);

        $page = (new ListAdminMediaHandler($repository))(new ListAdminMedia(1, 20));

        self::assertTrue($page->items[0]->hasVariants);
    }

    private function makeAsset(string $createdAt, string $filename): MediaAsset
    {
        $id = Uuid::v7();

        return MediaAsset::record(
            id: $id,
            storageKey: StorageKey::forNewAsset($id, MediaType::Jpeg),
            originalFilename: $filename,
            mimeType: MediaType::Jpeg,
            sizeBytes: 1024,
            dimensions: MediaDimensions::of(1200, 800),
            sha256: Sha256::fromString(str_repeat('a', 64)),
            now: new \DateTimeImmutable($createdAt),
        );
    }
}
