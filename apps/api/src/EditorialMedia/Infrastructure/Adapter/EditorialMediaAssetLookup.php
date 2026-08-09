<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Adapter;

use App\Editorial\Application\Media\MediaAssetDescriptor;
use App\Editorial\Application\Media\MediaAssetLookupInterface;
use App\Editorial\Application\Media\MediaAssetReaderInterface;
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

        return new MediaAssetDescriptor(
            id: $asset->id(),
            width: $asset->width(),
            height: $asset->height(),
            mimeType: $asset->mimeType()->mime(),
            sizeBytes: $asset->sizeBytes(),
            sha256: $asset->sha256()->toString(),
        );
    }
}
