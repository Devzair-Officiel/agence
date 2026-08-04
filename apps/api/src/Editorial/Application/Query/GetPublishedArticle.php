<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\ArticleSlug;

/**
 * Query DTO — récupération d'un article publié par slug.
 *
 * La validation du slug est déléguée au VO `ArticleSlug` : une chaîne
 * malformée lève `ArticleInvariantViolation` (traduite en 400 par le
 * controller).
 */
final class GetPublishedArticle
{
    public function __construct(
        public readonly ArticleSlug $slug,
    ) {
    }

    public static function fromInput(string $slug): self
    {
        return new self(ArticleSlug::fromString($slug));
    }
}
