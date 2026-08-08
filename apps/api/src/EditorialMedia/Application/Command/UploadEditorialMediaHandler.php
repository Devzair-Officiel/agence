<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Command;

use App\Editorial\Domain\Clock\ClockInterface;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Image\ImageProcessorInterface;
use App\EditorialMedia\Application\Mime\MimeTypeDetectorInterface;
use App\EditorialMedia\Application\Policy\MediaUploadPolicy;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use App\EditorialMedia\Domain\StorageKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Orchestre le téléversement d'un média éditorial en 9A.
 *
 * Pipeline strictement ordonné :
 *   1. Détection MIME serveur (`finfo`) — jamais l'extension du fichier.
 *   2. Contrôle de la policy sur le MIME et la taille brute.
 *   3. Décodage + réencodage par `ImageProcessorInterface` → fichier normalisé
 *      dans un dossier temporaire, sans EXIF/GPS ni commentaires.
 *   4. Contrôle des dimensions renvoyées par le processeur.
 *   5. Attribution d'un UUID v7 côté serveur, calcul de la `StorageKey`
 *      canonique et matérialisation du fichier via `MediaStorageInterface`.
 *   6. Persistance Doctrine ; en cas d'échec de flush, compensation en
 *      supprimant le fichier stocké pour préserver l'invariant « un asset
 *      Doctrine ⇔ un fichier sur disque ».
 *
 * Aucun code HTTP n'apparaît ici. Aucune exception d'implémentation (Doctrine,
 * GD, filesystem) ne fuit vers l'appelant — elles sont traduites en exceptions
 * applicatives typées via les ports.
 */
final class UploadEditorialMediaHandler
{
    public function __construct(
        private readonly MimeTypeDetectorInterface $mimeDetector,
        private readonly MediaUploadPolicy $policy,
        private readonly ImageProcessorInterface $imageProcessor,
        private readonly MediaStorageInterface $storage,
        private readonly MediaAssetRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(UploadEditorialMedia $command): UploadEditorialMediaResult
    {
        $sizeBytes = @filesize($command->sourcePath);
        if ($sizeBytes === false) {
            throw MediaStorageException::readFailed();
        }
        $this->policy->assertSizeAccepted($sizeBytes);

        $mime = $this->mimeDetector->detect($command->sourcePath);
        $type = $this->policy->assertMimeAccepted($mime);

        $normalized = $this->imageProcessor->normalize($command->sourcePath, $type);
        $this->policy->assertDimensionsAccepted($normalized->dimensions);
        $this->policy->assertSizeAccepted($normalized->sizeBytes);

        $id = Uuid::v7();
        $storageKey = StorageKey::forNewAsset($id, $normalized->mimeType);

        try {
            $this->storage->moveInto($normalized->temporaryPath, $storageKey);
        } catch (MediaStorageException $e) {
            @unlink($normalized->temporaryPath);
            throw $e;
        }

        $asset = MediaAsset::record(
            $id,
            $storageKey,
            $command->originalFilename,
            $normalized->mimeType,
            $normalized->sizeBytes,
            $normalized->dimensions,
            $normalized->sha256,
            $this->clock->now(),
        );

        try {
            $this->repository->save($asset);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            try {
                $this->storage->delete($storageKey);
            } catch (MediaStorageException) {
                // compensation best-effort ; l'incident originel reste prioritaire
            }
            throw $e;
        }

        return new UploadEditorialMediaResult($asset);
    }
}
