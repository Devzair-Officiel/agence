<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Image;

use App\EditorialMedia\Domain\Sha256;

/**
 * Métadonnées d'un variant WebP généré et vérifié (Phase 9C).
 *
 * `temporaryPath` pointe vers un fichier WebP dans le répertoire temporaire
 * système. Le handler est responsable de son déplacement ou de sa suppression.
 */
final class ImageVariantResult
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $temporaryPath,
        public readonly int $width,
        public readonly int $height,
        public readonly int $sizeBytes,
        public readonly Sha256 $sha256,
    ) {
    }
}
