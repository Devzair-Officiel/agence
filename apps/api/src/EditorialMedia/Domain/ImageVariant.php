<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain;

enum ImageVariant: string
{
    case Card = 'card';
    case Hero = 'hero';

    /** Facteur k de plafond nominal (width = 16×k, height = 9×k). */
    public function kCap(): int
    {
        return match ($this) {
            self::Card => 48,  // 768 × 432
            self::Hero => 100, // 1600 × 900
        };
    }
}
