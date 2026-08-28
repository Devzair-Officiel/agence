<?php

declare(strict_types=1);

namespace App\Admin\Application\Query;

use App\Editorial\Application\Query\AdminArticleListItem;

/**
 * Résumé éditorial du tableau de bord administrateur (Phase R2).
 *
 * Agrège les compteurs par statut, le nombre de médias et les cinq derniers
 * articles modifiés. Construit exclusivement par `GetAdminDashboardSummaryHandler`.
 */
final class AdminDashboardSummary
{
    /**
     * @param list<AdminArticleListItem> $recentArticles
     */
    public function __construct(
        public readonly int $published,
        public readonly int $drafts,
        public readonly int $archived,
        public readonly int $mediaCount,
        public readonly array $recentArticles,
    ) {
    }
}
