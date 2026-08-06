<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\Article;

/**
 * Résultat structuré d'une mise à jour. `mutated` distingue le cas
 * no-op (toutes les valeurs fournies étaient identiques aux valeurs en
 * base — `updatedAt` inchangé) du cas où au moins un champ a changé.
 */
final class UpdateDraftArticleResult
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
