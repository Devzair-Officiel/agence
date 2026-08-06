<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\Article;

/**
 * Résultat structuré d'un archivage. Distingue le cas idempotent
 * (déjà archivé — pas de modification) du cas où le statut change.
 */
final class ArchiveArticleResult
{
    private function __construct(
        public readonly Article $article,
        public readonly bool $alreadyArchived,
    ) {
    }

    public static function archived(Article $article): self
    {
        return new self($article, false);
    }

    public static function alreadyArchived(Article $article): self
    {
        return new self($article, true);
    }
}
