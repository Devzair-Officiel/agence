<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Adapter;

use App\Editorial\Application\Media\PublicMediaResource;
use App\Editorial\Application\Media\PublicMediaVariantStreamerInterface;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaVariantStorageInterface;
use App\EditorialMedia\Domain\ImageVariant;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use App\EditorialMedia\Domain\VariantStorageKey;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Adaptateur ACL : implémente `PublicMediaVariantStreamerInterface` en
 * s'appuyant sur le repository et le storage du contexte `EditorialMedia`.
 *
 * Renvoie `null` dans trois cas :
 *   1. L'asset n'existe pas en DB.
 *   2. Le variant n'a pas encore été généré (asset legacy sans variants).
 *   3. Le fichier physique du variant est illisible.
 */
final class EditorialMediaVariantStreamer implements PublicMediaVariantStreamerInterface
{
    public function __construct(
        private readonly MediaAssetRepositoryInterface $repository,
        private readonly MediaVariantStorageInterface $variantStorage,
        private readonly LoggerInterface $editorialMediaLogger,
    ) {
    }

    public function openVariant(Uuid $mediaAssetId, ImageVariant $variant): ?PublicMediaResource
    {
        $asset = $this->repository->findById($mediaAssetId);
        if (!$asset instanceof MediaAsset) {
            return null;
        }

        $variantKey = match ($variant) {
            ImageVariant::Card => $asset->cardStorageKey(),
            ImageVariant::Hero => $asset->heroStorageKey(),
        };

        if ($variantKey === null) {
            // Asset legacy sans variants — le front doit tomber en fallback.
            return null;
        }

        $sha256 = match ($variant) {
            ImageVariant::Card => $asset->cardSha256(),
            ImageVariant::Hero => $asset->heroSha256(),
        };

        $sizeBytes = match ($variant) {
            ImageVariant::Card => $asset->cardSizeBytes(),
            ImageVariant::Hero => $asset->heroSizeBytes(),
        };

        if ($sha256 === null || $sizeBytes === null) {
            return null;
        }

        try {
            $stream = $this->variantStorage->openVariantReadStream($variantKey);
        } catch (MediaStorageException) {
            $this->editorialMediaLogger->warning('editorial_media.variant_stream.storage_error', [
                'media_id' => $mediaAssetId->toRfc4122(),
                'variant'  => $variant->value,
            ]);

            return null;
        }

        return new PublicMediaResource(
            stream: $stream,
            mimeType: 'image/webp',
            sizeBytes: $sizeBytes,
            sha256: $sha256->toString(),
            lastModified: $asset->createdAt(),
        );
    }
}
