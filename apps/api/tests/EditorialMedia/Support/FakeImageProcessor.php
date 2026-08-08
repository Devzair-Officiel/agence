<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Support;

use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Application\Image\ImageProcessorInterface;
use App\EditorialMedia\Application\Image\NormalizedImage;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;

/**
 * Faux processeur pour les tests du handler. Ne touche pas à GD ; produit un
 * fichier temporaire vide via `tempnam` (le handler ne lit pas le contenu).
 *
 * Trace le dernier chemin temporaire produit pour permettre aux tests de
 * vérifier la compensation (unlink) sur échec de stockage.
 */
final class FakeImageProcessor implements ImageProcessorInterface
{
    public ?string $lastTemporaryPath = null;
    public int $normalizeCallCount = 0;

    public function __construct(
        private readonly ?MediaType $forcedType = null,
        private readonly ?MediaDimensions $forcedDimensions = null,
        private readonly ?int $forcedSizeBytes = null,
        private readonly ?Sha256 $forcedSha256 = null,
        private readonly ?\Throwable $throwable = null,
    ) {
    }

    public function normalize(string $sourcePath, MediaType $declaredType): NormalizedImage
    {
        ++$this->normalizeCallCount;

        if ($this->throwable !== null) {
            throw $this->throwable;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'fake-media-');
        if ($tmp === false) {
            throw new InvalidImageException('Impossible de créer un fichier temporaire.');
        }

        $this->lastTemporaryPath = $tmp;

        return new NormalizedImage(
            temporaryPath: $tmp,
            mimeType: $this->forcedType ?? $declaredType,
            dimensions: $this->forcedDimensions ?? MediaDimensions::of(1200, 800),
            sizeBytes: $this->forcedSizeBytes ?? 4096,
            sha256: $this->forcedSha256 ?? Sha256::fromString(str_repeat('a', 64)),
        );
    }
}
