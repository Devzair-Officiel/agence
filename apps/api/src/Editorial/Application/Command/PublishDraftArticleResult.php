<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\Article;

/**
 * Résultat structuré d'une publication admin. Rappel : le handler exige
 * `Draft` strict et lève sinon — le contrôleur ne verra donc pas de
 * variante « déjà publié » ou « restauration requise ».
 */
final class PublishDraftArticleResult
{
    public function __construct(
        public readonly Article $article,
    ) {
    }
}
