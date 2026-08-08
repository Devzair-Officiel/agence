<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;

/**
 * Dimensions d'une image normalisée. Portées comme value object pour
 * imposer les invariants (> 0, entiers) en un seul endroit.
 *
 * Les bornes hautes (max dimensions, max pixels totaux) ne sont PAS portées
 * ici : elles appartiennent à la policy applicative
 * (`MediaUploadPolicy`) qui peut évoluer indépendamment.
 */
final class MediaDimensions
{
    private function __construct(
        private readonly int $width,
        private readonly int $height,
    ) {
    }

    public static function of(int $width, int $height): self
    {
        if ($width <= 0 || $height <= 0) {
            throw new EditorialMediaInvariantViolation(\sprintf(
                'Les dimensions doivent être strictement positives (reçu %dx%d).',
                $width,
                $height,
            ));
        }

        return new self($width, $height);
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function totalPixels(): int
    {
        return $this->width * $this->height;
    }
}
