<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Command;

use App\Editorial\Domain\Clock\ClockInterface;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Image\ImageVariantProcessorInterface;
use App\EditorialMedia\Application\Image\ImageVariantResult;
use App\EditorialMedia\Application\Image\ImageVariantsResult;
use App\EditorialMedia\Application\Image\ImageProcessorInterface;
use App\EditorialMedia\Application\Mime\MimeTypeDetectorInterface;
use App\EditorialMedia\Application\Policy\MediaUploadPolicy;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Application\Storage\MediaVariantStorageInterface;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use App\EditorialMedia\Domain\MediaVariantRecord;
use App\EditorialMedia\Domain\StorageKey;
use App\EditorialMedia\Domain\VariantStorageKey;
use App\EditorialMedia\Domain\ImageVariant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Orchestre le téléversement d'un média éditorial (Phase 9A/9C).
 *
 * Pipeline atomique à trois fichiers (Phase 9C) :
 *   1. Validation MIME + taille brute.
 *   2. Normalisation GD → fichier temporaire A (original).
 *   3. Validation dimensions normalisées.
 *   4. Génération variants WebP → fichiers temporaires B (card) + C (hero).
 *      L'image source est décodée une fois ; chaque bitmap est libéré après
 *      encodage. B et C contiennent sha256 + dimensions réelles.
 *   5. Déplacement atomique : A → original, B → card, C → hero.
 *      Toute erreur intermédiaire supprime les fichiers déjà déplacés.
 *   6. Construction du `MediaAsset` avec les variants attachés.
 *   7. Persistance Doctrine ; échec → suppression best-effort des 3 fichiers.
 *
 * Invariant garanti : « un `MediaAsset` en DB ⟺ trois fichiers cohérents
 * sur le disque » pour tout upload via ce handler.
 */
final class UploadEditorialMediaHandler
{
    public function __construct(
        private readonly MimeTypeDetectorInterface $mimeDetector,
        private readonly MediaUploadPolicy $policy,
        private readonly ImageProcessorInterface $imageProcessor,
        private readonly ImageVariantProcessorInterface $variantProcessor,
        private readonly MediaStorageInterface $storage,
        private readonly MediaVariantStorageInterface $variantStorage,
        private readonly MediaAssetRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(UploadEditorialMedia $command): UploadEditorialMediaResult
    {
        // ── 1. Validation taille + MIME ────────────────────────────────────
        $sizeBytes = @filesize($command->sourcePath);
        if ($sizeBytes === false) {
            throw MediaStorageException::readFailed();
        }
        $this->policy->assertSizeAccepted($sizeBytes);

        $mime = $this->mimeDetector->detect($command->sourcePath);
        $type = $this->policy->assertMimeAccepted($mime);

        // ── 2-3. Normalisation + validation dimensions ─────────────────────
        $normalized = $this->imageProcessor->normalize($command->sourcePath, $type);
        $this->policy->assertDimensionsAccepted($normalized->dimensions);
        $this->policy->assertSizeAccepted($normalized->sizeBytes);

        // ── 4. Génération variants (charge la source une seule fois) ────────
        $variants = $this->variantProcessor->generateVariants(
            $normalized->temporaryPath,
            $normalized->mimeType,
        );

        // ── 5. Déplacement atomique des 3 fichiers ─────────────────────────
        $id = Uuid::v7();
        $originalKey = StorageKey::forNewAsset($id, $normalized->mimeType);
        $cardKey     = VariantStorageKey::forVariant($id, ImageVariant::Card);
        $heroKey     = VariantStorageKey::forVariant($id, ImageVariant::Hero);

        $this->moveAllOrCompensate($normalized->temporaryPath, $originalKey, $variants, $cardKey, $heroKey);

        // ── 6. Création du MediaAsset avec variants ────────────────────────
        $asset = MediaAsset::record(
            $id,
            $originalKey,
            $command->originalFilename,
            $normalized->mimeType,
            $normalized->sizeBytes,
            $normalized->dimensions,
            $normalized->sha256,
            $this->clock->now(),
        );

        $asset->attachVariants(
            new MediaVariantRecord($cardKey, $variants->card->width, $variants->card->height, $variants->card->sizeBytes, $variants->card->sha256),
            new MediaVariantRecord($heroKey, $variants->hero->width, $variants->hero->height, $variants->hero->sizeBytes, $variants->hero->sha256),
        );

        // ── 7. Persistance avec compensation ──────────────────────────────
        try {
            $this->repository->save($asset);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            foreach ([$heroKey, $cardKey] as $vk) {
                try {
                    $this->variantStorage->deleteVariant($vk);
                } catch (MediaStorageException) {
                }
            }
            try {
                $this->storage->delete($originalKey);
            } catch (MediaStorageException) {
            }
            throw $e;
        }

        return new UploadEditorialMediaResult($asset);
    }

    /**
     * Déplace les 3 fichiers temporaires vers le stockage final.
     *
     * Ordre : original → card → hero (du moins dépendant au plus dépendant).
     * Si une étape échoue, les fichiers déjà déplacés sont supprimés
     * (best-effort) et le fichier temporaire non encore déplacé est unlinké.
     */
    private function moveAllOrCompensate(
        string $originalTmp,
        StorageKey $originalKey,
        ImageVariantsResult $variants,
        VariantStorageKey $cardKey,
        VariantStorageKey $heroKey,
    ): void {
        try {
            $this->storage->moveInto($originalTmp, $originalKey);
        } catch (MediaStorageException $e) {
            @unlink($originalTmp);
            @unlink($variants->card->temporaryPath);
            @unlink($variants->hero->temporaryPath);
            throw $e;
        }

        try {
            $this->variantStorage->moveVariantInto($variants->card->temporaryPath, $cardKey);
        } catch (MediaStorageException $e) {
            try {
                $this->storage->delete($originalKey);
            } catch (MediaStorageException) {
            }
            @unlink($variants->card->temporaryPath);
            @unlink($variants->hero->temporaryPath);
            throw $e;
        }

        try {
            $this->variantStorage->moveVariantInto($variants->hero->temporaryPath, $heroKey);
        } catch (MediaStorageException $e) {
            try {
                $this->variantStorage->deleteVariant($cardKey);
            } catch (MediaStorageException) {
            }
            try {
                $this->storage->delete($originalKey);
            } catch (MediaStorageException) {
            }
            @unlink($variants->hero->temporaryPath);
            throw $e;
        }
    }
}
