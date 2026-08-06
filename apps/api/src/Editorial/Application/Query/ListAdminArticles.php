<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\ArticleStatus;

/**
 * Requête paginée pour l'écran de liste admin. `page` et `perPage` bornés
 * en amont par le contrôleur (page ≥ 1 clampée, perPage = 20 fixe en 8C3).
 */
final class ListAdminArticles
{
    /**
     * @param int<1, max> $page
     * @param int<1, max> $perPage
     */
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly ?ArticleStatus $statusFilter,
    ) {
    }
}
