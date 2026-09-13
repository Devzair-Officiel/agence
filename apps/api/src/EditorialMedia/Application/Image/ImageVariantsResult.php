<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Image;

/**
 * Résultat de la génération des deux variants WebP (Phase 9C).
 *
 * Chaque variant est vérifié (sha256, taille, décodabilité) avant que ce
 * DTO ne soit construit. Les fichiers temporaires référencés sont sous la
 * responsabilité du handler : il doit les déplacer vers le stockage final
 * ou les supprimer en cas d'échec.
 */
final class ImageVariantsResult
{
    public function __construct(
        public readonly ImageVariantResult $card,
        public readonly ImageVariantResult $hero,
    ) {
    }
}
