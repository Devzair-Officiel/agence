<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\Article;

/**
 * Résultat structuré d'un import Markdown. Facilite les assertions dans
 * les tests (statut + article construit) et laisse la commande CLI décider
 * du rendu utilisateur.
 */
final class ImportArticleResult
{
    private function __construct(
        public readonly Article $article,
        public readonly bool $persisted,
    ) {
    }

    public static function dryRun(Article $article): self
    {
        return new self($article, false);
    }

    public static function persisted(Article $article): self
    {
        return new self($article, true);
    }
}
