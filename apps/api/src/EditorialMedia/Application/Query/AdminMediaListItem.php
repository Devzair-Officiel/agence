<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Query;

use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaType;

/**
 * Ligne du tableau d'administration des médias — vue plate, sans référence
 * Doctrine ni chemin filesystem. Sert exclusivement au rendu Twig.
 */
final class AdminMediaListItem
{
    public function __construct(
        public readonly string $id,
        public readonly string $originalFilename,
        public readonly MediaType $mimeType,
        public readonly int $sizeBytes,
        public readonly int $width,
        public readonly int $height,
        public readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function fromEntity(MediaAsset $asset): self
    {
        return new self(
            id: $asset->id()->toRfc4122(),
            originalFilename: $asset->originalFilename(),
            mimeType: $asset->mimeType(),
            sizeBytes: $asset->sizeBytes(),
            width: $asset->width(),
            height: $asset->height(),
            createdAt: $asset->createdAt(),
        );
    }
}
