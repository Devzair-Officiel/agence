<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Storage;

use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Domain\StorageKey;

/**
 * Port sortant : la couche Application demande à Infrastructure de matérialiser
 * un fichier normalisé dans le stockage privé, sous une `StorageKey` fixée par
 * le domaine.
 *
 * Sémantique attendue de l'implémentation par défaut (`LocalMediaStorage`) :
 *   - `moveInto` déplace atomiquement le fichier temporaire du processeur vers
 *     l'emplacement final. En cas d'échec, le fichier temporaire reste au même
 *     endroit — c'est à l'appelant de le supprimer.
 *   - `openReadStream` renvoie une ressource lecture pour servir le fichier
 *     en streaming côté HTTP. L'appelant doit refermer la ressource.
 *   - `delete` retire le fichier ET son dossier UUID parent si celui-ci est
 *     vide. Utilisé exclusivement par la compensation du handler (rollback
 *     après échec de flush Doctrine).
 *
 * Toutes les opérations qui échouent doivent lever `MediaStorageException` —
 * jamais une exception native filesystem, pour ne pas fuiter de chemins.
 */
interface MediaStorageInterface
{
    /**
     * @param non-empty-string $temporaryPath
     *
     * @throws MediaStorageException
     */
    public function moveInto(string $temporaryPath, StorageKey $destination): void;

    /**
     * @return resource
     *
     * @throws MediaStorageException
     */
    public function openReadStream(StorageKey $key);

    public function exists(StorageKey $key): bool;

    /**
     * @throws MediaStorageException
     */
    public function delete(StorageKey $key): void;
}
