<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\Article;

/**
 * Résultat structuré d'une création. Expose l'article créé pour que le
 * contrôleur puisse rediriger vers son écran d'édition (PRG).
 */
final class CreateDraftArticleResult
{
    public function __construct(
        public readonly Article $article,
    ) {
    }
}
