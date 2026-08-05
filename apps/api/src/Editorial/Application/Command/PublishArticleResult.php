<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\Article;

/**
 * Résultat structuré d'une publication. Distingue le cas idempotent
 * (déjà publié — pas de modification) du cas où le statut change.
 */
final class PublishArticleResult
{
    private function __construct(
        public readonly Article $article,
        public readonly bool $alreadyPublished,
    ) {
    }

    public static function published(Article $article): self
    {
        return new self($article, false);
    }

    public static function alreadyPublished(Article $article): self
    {
        return new self($article, true);
    }
}
