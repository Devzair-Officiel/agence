<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;
use App\EditorialMedia\Domain\StorageKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class MediaAssetTest extends TestCase
{
    public function testRecordExposesAllFieldsThroughGetters(): void
    {
        $id = Uuid::v7();
        $key = StorageKey::forNewAsset($id, MediaType::Jpeg);
        $now = new \DateTimeImmutable('2026-08-08T12:00:00+00:00');
        $hash = Sha256::fromString(str_repeat('a', 64));

        $asset = MediaAsset::record(
            id: $id,
            storageKey: $key,
            originalFilename: 'ma-photo.JPG',
            mimeType: MediaType::Jpeg,
            sizeBytes: 1024,
            dimensions: MediaDimensions::of(1200, 800),
            sha256: $hash,
            now: $now,
        );

        self::assertSame($id->toRfc4122(), $asset->id()->toRfc4122());
        self::assertSame($key->toString(), $asset->storageKey()->toString());
        self::assertSame('ma-photo.JPG', $asset->originalFilename());
        self::assertSame(MediaType::Jpeg, $asset->mimeType());
        self::assertSame(1024, $asset->sizeBytes());
        self::assertSame(1200, $asset->width());
        self::assertSame(800, $asset->height());
        self::assertSame($hash->toString(), $asset->sha256()->toString());
        self::assertEquals($now, $asset->createdAt());
    }

    public function testTruncatesOriginalFilenameAtLimit(): void
    {
        $longName = str_repeat('é', 260) . '.jpg';

        $asset = self::validMediaAsset(originalFilename: $longName);

        self::assertLessThanOrEqual(MediaAsset::ORIGINAL_FILENAME_MAX, mb_strlen($asset->originalFilename()));
    }

    public function testStripsControlCharsFromOriginalFilename(): void
    {
        $asset = self::validMediaAsset(originalFilename: "photo\0\x01\x02.jpg");

        self::assertSame('photo.jpg', $asset->originalFilename());
    }

    public function testRejectsEmptyOriginalFilename(): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);

        self::validMediaAsset(originalFilename: "   \0\x02   ");
    }

    public function testRejectsNonPositiveSize(): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);

        self::validMediaAsset(sizeBytes: 0);
    }

    public function testDimensionsFactoryRoundtrip(): void
    {
        $asset = self::validMediaAsset();

        $dim = $asset->dimensions();

        self::assertSame(1200, $dim->width());
        self::assertSame(800, $dim->height());
    }

    private static function validMediaAsset(
        string $originalFilename = 'ma-photo.jpg',
        int $sizeBytes = 1024,
    ): MediaAsset {
        $id = Uuid::v7();

        return MediaAsset::record(
            id: $id,
            storageKey: StorageKey::forNewAsset($id, MediaType::Jpeg),
            originalFilename: $originalFilename,
            mimeType: MediaType::Jpeg,
            sizeBytes: $sizeBytes,
            dimensions: MediaDimensions::of(1200, 800),
            sha256: Sha256::fromString(str_repeat('a', 64)),
            now: new \DateTimeImmutable('2026-08-08T12:00:00+00:00'),
        );
    }
}
