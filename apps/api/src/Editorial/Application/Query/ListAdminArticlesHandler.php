<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

/**
 * Handler minimal : délègue au port. On garde la couche pour uniformiser
 * les points d'entrée applicatifs (test/wire depuis les contrôleurs) et
 * pour permettre plus tard un décorateur (logging, cache) sans toucher aux
 * appelants.
 */
final class ListAdminArticlesHandler
{
    public function __construct(
        private readonly AdminArticleReadRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ListAdminArticles $query): AdminArticleListPage
    {
        $items = $this->repository->paginate($query->page, $query->perPage, $query->statusFilter);
        $total = $this->repository->count($query->statusFilter);

        return new AdminArticleListPage(
            items: $items,
            page: $query->page,
            perPage: $query->perPage,
            total: $total,
            statusFilter: $query->statusFilter,
        );
    }
}
