<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Exception;

/**
 * L'image décodée dépasse une des bornes hautes de `MediaUploadPolicy` :
 * largeur, hauteur, ou nombre total de pixels. Le contrôleur d'admin la
 * traduit en 422 Unprocessable Entity — le fichier a un MIME valide mais son
 * gabarit est refusé.
 */
final class ImageDimensionsExceededException extends \RuntimeException
{
    public static function forDimensions(
        int $width,
        int $height,
        int $maxWidth,
        int $maxHeight,
        int $maxTotalPixels,
    ): self {
        return new self(\sprintf(
            'Image %dx%d refusée : maximum autorisé %dx%d (%d pixels au total).',
            $width,
            $height,
            $maxWidth,
            $maxHeight,
            $maxTotalPixels,
        ));
    }

    public static function forTotalPixels(int $width, int $height, int $totalPixels, int $maxTotalPixels): self
    {
        return new self(\sprintf(
            'Image %dx%d refusée : %d pixels au total, maximum autorisé %d.',
            $width,
            $height,
            $totalPixels,
            $maxTotalPixels,
        ));
    }
}
