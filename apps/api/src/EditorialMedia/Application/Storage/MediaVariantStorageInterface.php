<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Storage;

use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Domain\VariantStorageKey;

/**
 * Port sortant : matérialise et lit les variants WebP dans le stockage privé
 * (Phase 9C).
 *
 * Séparé de `MediaStorageInterface` (original) par ISP : les appelants qui
 * ne traitent que l'original ne dépendent pas des méthodes variant.
 *
 * Sémantique identique à `MediaStorageInterface` :
 *   - `moveVariantInto` déplace le fichier temporaire vers l'emplacement final.
 *   - `openVariantReadStream` renvoie un flux binaire ; l'appelant referme.
 *   - `deleteVariant` supprime le fichier ; le répertoire UUID parent est
 *     nettoyé s'il est vide après suppression des trois fichiers.
 */
interface MediaVariantStorageInterface
{
    /**
     * @param non-empty-string $temporaryPath
     *
     * @throws MediaStorageException
     */
    public function moveVariantInto(string $temporaryPath, VariantStorageKey $destination): void;

    /**
     * @return resource
     *
     * @throws MediaStorageException
     */
    public function openVariantReadStream(VariantStorageKey $key);

    public function variantExists(VariantStorageKey $key): bool;

    /**
     * @throws MediaStorageException
     */
    public function deleteVariant(VariantStorageKey $key): void;
}
