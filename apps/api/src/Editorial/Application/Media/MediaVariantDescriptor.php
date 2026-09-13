<?php

declare(strict_types=1);

namespace App\Editorial\Application\Media;

/**
 * DTO immuable décrivant un variant WebP d'un `MediaAsset` (Phase 9C).
 *
 * Consommé par `PublicArticleImageView` pour construire le contrat JSON
 * `card` / `hero`. Pas de `sha256` exposé : l'ETag est géré par le
 * contrôleur de serve variant, pas par la vue article.
 */
final class MediaVariantDescriptor
{
    public function __construct(
        public readonly string $url,
        public readonly int $width,
        public readonly int $height,
        public readonly string $sha256,
    ) {
    }
}
