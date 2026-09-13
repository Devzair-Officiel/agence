<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain;

/**
 * Données d'un variant WebP généré, transmises au moment de l'enregistrement
 * du `MediaAsset` (Phase 9C).
 *
 * Immuable : construit par le handler après génération et vérification des
 * fichiers, jamais modifié ensuite.
 */
final class MediaVariantRecord
{
    public function __construct(
        public readonly VariantStorageKey $storageKey,
        public readonly int $width,
        public readonly int $height,
        public readonly int $sizeBytes,
        public readonly Sha256 $sha256,
    ) {
    }
}
