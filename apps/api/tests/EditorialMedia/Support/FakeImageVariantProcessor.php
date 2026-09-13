<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Support;

use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Application\Image\ImageVariantProcessorInterface;
use App\EditorialMedia\Application\Image\ImageVariantResult;
use App\EditorialMedia\Application\Image\ImageVariantsResult;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;

/**
 * Faux processeur variant pour les tests unitaires du handler.
 *
 * Produit deux fichiers temporaires réels (vides) afin que le handler puisse
 * les déplacer via `FakeMediaStorage`. Les dimensions par défaut sont
 * exactement 16:9 (768×432 card, 800×450 hero) — modifiables via le
 * constructeur pour les cas limites.
 */
final class FakeImageVariantProcessor implements ImageVariantProcessorInterface
{
    public int $generateVariantsCallCount = 0;
    public ?\Throwable $throwable = null;

    public function __construct(
        private readonly int $cardWidth   = 768,
        private readonly int $cardHeight  = 432,
        private readonly int $heroWidth   = 800,
        private readonly int $heroHeight  = 450,
    ) {
    }

    public function generateVariants(string $normalizedPath, MediaType $type): ImageVariantsResult
    {
        ++$this->generateVariantsCallCount;

        if ($this->throwable !== null) {
            throw $this->throwable;
        }

        return new ImageVariantsResult(
            card: $this->makeTmpResult($this->cardWidth, $this->cardHeight, 'card'),
            hero: $this->makeTmpResult($this->heroWidth, $this->heroHeight, 'hero'),
        );
    }

    private function makeTmpResult(int $w, int $h, string $prefix): ImageVariantResult
    {
        $tmp = tempnam(sys_get_temp_dir(), "fake-variant-{$prefix}-");
        if ($tmp === false) {
            throw new InvalidImageException('Impossible de créer un fichier temporaire.');
        }

        return new ImageVariantResult(
            temporaryPath: $tmp,
            width: $w,
            height: $h,
            sizeBytes: 1024,
            sha256: Sha256::fromString(str_repeat('b', 64)),
        );
    }
}
