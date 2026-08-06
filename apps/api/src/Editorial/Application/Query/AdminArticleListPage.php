<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\ArticleStatus;

/**
 * Page paginée retournée par `ListAdminArticlesHandler`. Inclut les
 * métadonnées de pagination (total, page courante, dernière page) que le
 * template Twig utilise pour construire les liens prev/next accessibles.
 */
final class AdminArticleListPage
{
    /**
     * @param list<AdminArticleListItem> $items
     * @param int<1, max>                $page
     * @param int<1, max>                $perPage
     * @param int<0, max>                $total
     */
    public function __construct(
        public readonly array $items,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
        public readonly ?ArticleStatus $statusFilter,
    ) {
    }

    /**
     * @return int<1, max>
     */
    public function lastPage(): int
    {
        if ($this->total === 0) {
            return 1;
        }

        return (int) ceil($this->total / $this->perPage);
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->lastPage();
    }
}
