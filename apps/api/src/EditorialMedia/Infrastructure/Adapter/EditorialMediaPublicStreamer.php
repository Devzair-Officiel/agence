<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Adapter;

use App\Editorial\Application\Media\PublicMediaResource;
use App\Editorial\Application\Media\PublicMediaStreamerInterface;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Adaptateur ANTI-CORRUPTION-LAYER : implémente le port
 * `PublicMediaStreamerInterface` d'`Editorial` en s'appuyant sur les
 * services internes d'`EditorialMedia` (`MediaAssetRepositoryInterface`
 * + `MediaStorageInterface`).
 *
 * Séparé de `EditorialMediaAssetLookup` (qui répond aux questions
 * métadonnées) pour rester lisible : ici on ouvre un flux binaire, là on
 * lit un `MediaAssetDescriptor`. Deux responsabilités, deux adaptateurs.
 *
 * Renvoie `null` si :
 *   - le média n'existe plus côté DB (incohérence rare : la porte a validé
 *     l'existence via l'article publié, mais l'asset a été détruit entre
 *     les deux) ;
 *   - le fichier physique n'est plus lisible (`MediaStorageException`).
 * Le contrôleur traduit dans les deux cas en 404 — la 500 est réservée aux
 * bugs, pas aux incohérences d'infra. On loggue l'incohérence pour
 * diagnostic ultérieur.
 */
final class EditorialMediaPublicStreamer implements PublicMediaStreamerInterface
{
    public function __construct(
        private readonly MediaAssetRepositoryInterface $repository,
        private readonly MediaStorageInterface $storage,
        private readonly LoggerInterface $editorialMediaLogger,
    ) {
    }

    public function open(Uuid $mediaAssetId): ?PublicMediaResource
    {
        $asset = $this->repository->findById($mediaAssetId);
        if (!$asset instanceof MediaAsset) {
            $this->editorialMediaLogger->warning('editorial_media.public_stream.asset_missing', [
                'media_id' => $mediaAssetId->toRfc4122(),
            ]);

            return null;
        }

        try {
            $stream = $this->storage->openReadStream($asset->storageKey());
        } catch (MediaStorageException) {
            $this->editorialMediaLogger->warning('editorial_media.public_stream.storage_error', [
                'media_id' => $asset->id()->toRfc4122(),
            ]);

            return null;
        }

        return new PublicMediaResource(
            stream: $stream,
            mimeType: $asset->mimeType()->mime(),
            sizeBytes: $asset->sizeBytes(),
            sha256: $asset->sha256()->toString(),
            lastModified: $asset->createdAt(),
        );
    }
}
