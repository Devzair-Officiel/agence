<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Policy;

use App\EditorialMedia\Application\Exception\ImageDimensionsExceededException;
use App\EditorialMedia\Application\Exception\MediaTooLargeException;
use App\EditorialMedia\Application\Exception\UnsupportedMediaTypeException;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;

/**
 * Politique unique et centralisée des limites acceptables sur un téléversement.
 *
 * Les bornes sont volontairement conservatives : on préfère refuser une image
 * hors gabarit plutôt que de faire tomber le serveur ou de saturer le stockage.
 * Toutes les valeurs sont exprimées dans le type source : octets pour le poids,
 * pixels pour les dimensions. Modifier ces constantes est le seul point à
 * ajuster pour resserrer ou desserrer la porte d'entrée.
 *
 * La policy ne connaît ni HTTP, ni Doctrine, ni GD ; elle ne fait que
 * comparer des nombres et lever des exceptions applicatives typées. Les
 * contrôleurs et les tests HTTP ne doivent JAMAIS répliquer ces vérifications.
 */
final class MediaUploadPolicy
{
    public const MAX_SIZE_BYTES = 8 * 1024 * 1024;
    public const MAX_WIDTH = 8000;
    public const MAX_HEIGHT = 8000;
    public const MAX_TOTAL_PIXELS = 40_000_000;

    public function assertMimeAccepted(string $mime): MediaType
    {
        $type = MediaType::tryFromMime($mime);

        if ($type === null) {
            throw UnsupportedMediaTypeException::forMime($mime, MediaType::allowedMimes());
        }

        return $type;
    }

    public function assertSizeAccepted(int $sizeBytes): void
    {
        if ($sizeBytes <= 0 || $sizeBytes > self::MAX_SIZE_BYTES) {
            throw MediaTooLargeException::forSize($sizeBytes, self::MAX_SIZE_BYTES);
        }
    }

    public function assertDimensionsAccepted(MediaDimensions $dimensions): void
    {
        if ($dimensions->width() > self::MAX_WIDTH || $dimensions->height() > self::MAX_HEIGHT) {
            throw ImageDimensionsExceededException::forDimensions(
                $dimensions->width(),
                $dimensions->height(),
                self::MAX_WIDTH,
                self::MAX_HEIGHT,
                self::MAX_TOTAL_PIXELS,
            );
        }

        if ($dimensions->totalPixels() > self::MAX_TOTAL_PIXELS) {
            throw ImageDimensionsExceededException::forTotalPixels(
                $dimensions->width(),
                $dimensions->height(),
                $dimensions->totalPixels(),
                self::MAX_TOTAL_PIXELS,
            );
        }
    }
}
