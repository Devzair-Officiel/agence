<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Command;

use App\EditorialMedia\Application\Exception\MediaInUseException;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Application\Storage\MediaVariantStorageInterface;
use App\EditorialMedia\Domain\Exception\MediaAssetNotFoundException;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Supprime un média éditorial (Phase 9D).
 *
 * Stratégie : fichiers d'abord, base de données ensuite.
 *
 * Ordre de suppression pour limiter l'état incohérent en cas d'erreur :
 *   1. Vérification que le média existe (404 si absent).
 *   2. Vérification que le média n'est pas référencé (409 si utilisé).
 *   3. Suppression best-effort des fichiers physiques (original + variants).
 *      Si un fichier est déjà absent, on continue (idempotence).
 *   4. Suppression Doctrine + flush — si le flush échoue, les fichiers ont
 *      déjà été effacés ; la ligne DB est l'état de vérité de l'inventaire.
 *      Un nettoyage ultérieur (`CleanupOrphanMediaCommand`) pourra retirer la
 *      ligne orpheline si besoin (cas extrême de crash entre 3 et 4).
 *
 * @throws MediaAssetNotFoundException si l'UUID est inconnu
 * @throws MediaInUseException         si le média est référencé par un article
 * @throws MediaStorageException       si la suppression physique échoue
 */
final class DeleteEditorialMediaHandler
{
    public function __construct(
        private readonly MediaAssetRepositoryInterface $repository,
        private readonly MediaStorageInterface $storage,
        private readonly MediaVariantStorageInterface $variantStorage,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return string MIME type de l'asset supprimé (pour l'audit)
     *
     * @throws MediaAssetNotFoundException si l'UUID est inconnu
     * @throws MediaInUseException         si le média est référencé par un article
     * @throws MediaStorageException       si la suppression physique échoue
     */
    public function __invoke(DeleteEditorialMedia $command): string
    {
        $asset = $this->repository->getById($command->id);

        if ($this->repository->isReferencedByAnyArticle($command->id)) {
            throw MediaInUseException::referencedByArticle($command->id->toRfc4122());
        }

        // Suppression best-effort des variants (card, hero) puis de l'original.
        // MediaStorageException si le fichier est présent mais illisible (on re-lève).
        // Si le fichier est déjà absent, LocalMediaStorage::delete() ne lève pas.
        $cardKey = $asset->cardStorageKey();
        if ($cardKey !== null) {
            $this->variantStorage->deleteVariant($cardKey);
        }

        $heroKey = $asset->heroStorageKey();
        if ($heroKey !== null) {
            $this->variantStorage->deleteVariant($heroKey);
        }

        $this->storage->delete($asset->storageKey());

        $mime = $asset->mimeType()->mime();

        $this->repository->delete($asset);
        $this->entityManager->flush();

        return $mime;
    }
}
