<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use Symfony\Component\Uid\Uuid;

/**
 * Suppression définitive d'un article archivé.
 *
 * Règle métier : seul un article en statut `Archived` peut être supprimé.
 * Toute tentative sur un brouillon ou un article publié lève
 * `ArticleNotDeletableException` dans le handler.
 *
 * Cette suppression est irréversible et retire l'article de la base de
 * données. Les médias associés (heroMediaId) sont conservés : l'architecture
 * inter-contexte n'impose pas de propriété exclusive du média par l'article.
 */
final class DeleteArchivedArticle
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }
}
