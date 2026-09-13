<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Adapter;

use App\Editorial\Application\Media\MediaAssetDescriptor;
use App\Editorial\Application\Media\MediaAssetLookupInterface;
use App\Editorial\Application\Media\MediaAssetReaderInterface;
use App\Editorial\Application\Media\MediaVariantDescriptor;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Adaptateur ANTI-CORRUPTION-LAYER entre les contextes bornés `Editorial`
 * (aval) et `EditorialMedia` (amont).
 *
 * Implémente deux ports du domaine `Editorial` :
 *   - `MediaAssetLookupInterface` — question binaire pour les handlers de
 *     mutation qui valident un rattachement avant écriture ;
 *   - `MediaAssetReaderInterface` — chargement des métadonnées pour les
 *     read-models (admin et publique).
 *
 * Cet adaptateur est le SEUL point de contact entre les deux contextes.
 * Le domaine `Editorial` ne connaît ni `MediaAsset`, ni `MediaType`, ni
 * `MediaAssetRepositoryInterface` — il consomme uniquement les DTO plats
 * exposés par les ports (`MediaAssetDescriptor`).
 *
 * Aucune sortie ne fuite les internals `EditorialMedia` (aucun VO
 * `StorageKey`, `Sha256` typé, `MediaType` enum — seulement du scalaire
 * consommable par la présentation).
 */
final class EditorialMediaAssetLookup implements MediaAssetLookupInterface, MediaAssetReaderInterface
{
    public function __construct(
        private readonly MediaAssetRepositoryInterface $repository,
    ) {
    }

    public function exists(Uuid $mediaAssetId): bool
    {
        return $this->repository->findById($mediaAssetId) !== null;
    }

    public function findMetadata(Uuid $mediaAssetId): ?MediaAssetDescriptor
    {
        $asset = $this->repository->findById($mediaAssetId);

        if (!$asset instanceof MediaAsset) {
            return null;
        }

        $uuid = $asset->id()->toRfc4122();

        $card = ($asset->cardWidth() !== null && $asset->cardHeight() !== null && $asset->cardSha256() !== null)
            ? new MediaVariantDescriptor(
                url: '/api/media/' . $uuid . '/card',
                width: $asset->cardWidth(),
                height: $asset->cardHeight(),
                sha256: $asset->cardSha256()->toString(),
            )
            : null;

        $hero = ($asset->heroWidth() !== null && $asset->heroHeight() !== null && $asset->heroSha256() !== null)
            ? new MediaVariantDescriptor(
                url: '/api/media/' . $uuid . '/hero',
                width: $asset->heroWidth(),
                height: $asset->heroHeight(),
                sha256: $asset->heroSha256()->toString(),
            )
            : null;

        return new MediaAssetDescriptor(
            id: $asset->id(),
            width: $asset->width(),
            height: $asset->height(),
            mimeType: $asset->mimeType()->mime(),
            sizeBytes: $asset->sizeBytes(),
            sha256: $asset->sha256()->toString(),
            card: $card,
            hero: $hero,
        );
    }
}
