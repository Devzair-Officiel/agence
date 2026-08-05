<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\ArticleSlug;

/**
 * Publie un article existant. `publishedAt = null` → la publication utilise
 * l'instant présent fourni par `ClockInterface`. `publishedAt` explicite →
 * doit obligatoirement être dans le passé ou l'instant présent (refusé
 * sinon, aussi bien côté handler que côté agrégat).
 */
final class PublishArticleBySlug
{
    public function __construct(
        public readonly ArticleSlug $slug,
        public readonly ?\DateTimeImmutable $publishedAt = null,
    ) {
    }
}
