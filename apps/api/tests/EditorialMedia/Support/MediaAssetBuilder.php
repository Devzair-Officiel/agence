<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Support;

use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;
use App\EditorialMedia\Domain\StorageKey;
use Symfony\Component\Uid\Uuid;

/**
 * Fabrique fluide pour créer un `MediaAsset` dans les tests. Encapsule les
 * défauts raisonnables (JPEG 1200×800, 4 KiB, sha256 constant) pour ne
 * mentionner dans chaque test que le champ pertinent.
 */
final class MediaAssetBuilder
{
    private ?Uuid $id = null;
    private MediaType $type = MediaType::Jpeg;
    private string $filename = 'photo.jpg';
    private int $sizeBytes = 4096;
    private int $width = 1200;
    private int $height = 800;
    private string $sha256 = 'aabbccddeeff00112233445566778899aabbccddeeff00112233445566778899';
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('2026-08-08T10:00:00+00:00');
    }

    public function withId(Uuid $id): self
    {
        $c = clone $this;
        $c->id = $id;

        return $c;
    }

    public function withType(MediaType $type): self
    {
        $c = clone $this;
        $c->type = $type;

        return $c;
    }

    public function withOriginalFilename(string $filename): self
    {
        $c = clone $this;
        $c->filename = $filename;

        return $c;
    }

    public function withCreatedAt(string $isoUtc): self
    {
        $c = clone $this;
        $c->createdAt = new \DateTimeImmutable($isoUtc);

        return $c;
    }

    public function withSizeBytes(int $sizeBytes): self
    {
        $c = clone $this;
        $c->sizeBytes = $sizeBytes;

        return $c;
    }

    public function withDimensions(int $width, int $height): self
    {
        $c = clone $this;
        $c->width = $width;
        $c->height = $height;

        return $c;
    }

    public function build(): MediaAsset
    {
        $id = $this->id ?? Uuid::v7();

        return MediaAsset::record(
            id: $id,
            storageKey: StorageKey::forNewAsset($id, $this->type),
            originalFilename: $this->filename,
            mimeType: $this->type,
            sizeBytes: $this->sizeBytes,
            dimensions: MediaDimensions::of($this->width, $this->height),
            sha256: Sha256::fromString($this->sha256),
            now: $this->createdAt,
        );
    }
}
