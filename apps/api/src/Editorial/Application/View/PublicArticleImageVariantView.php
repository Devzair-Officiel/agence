<?php

declare(strict_types=1);

namespace App\Editorial\Application\View;

/**
 * DTO public d'un variant WebP (card ou hero) dans la réponse article
 * (Phase 9C).
 */
final class PublicArticleImageVariantView
{
    public function __construct(
        public readonly string $url,
        public readonly int $width,
        public readonly int $height,
    ) {
    }

    /**
     * @return array{url: string, width: int, height: int}
     */
    public function toArray(): array
    {
        return [
            'url'    => $this->url,
            'width'  => $this->width,
            'height' => $this->height,
        ];
    }
}
