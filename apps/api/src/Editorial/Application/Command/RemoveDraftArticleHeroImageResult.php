<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\Article;

/**
 * Résultat d'un `RemoveDraftArticleHeroImageHandler`.
 *
 * `mutated` distingue :
 *   - `true` : une image était présente et vient d'être retirée ;
 *   - `false` : aucun média n'était rattaché — no-op silencieux, aucun
 *     flush produit, `updatedAt` inchangé.
 */
final class RemoveDraftArticleHeroImageResult
{
    private function __construct(
        public readonly Article $article,
        public readonly bool $mutated,
    ) {
    }

    public static function mutated(Article $article): self
    {
        return new self($article, true);
    }

    public static function unchanged(Article $article): self
    {
        return new self($article, false);
    }
}
