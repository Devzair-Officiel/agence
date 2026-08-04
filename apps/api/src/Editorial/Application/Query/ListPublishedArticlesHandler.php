<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Application\View\ArticleSummaryView;
use App\Editorial\Application\View\PaginationView;
use App\Editorial\Domain\ArticleRepositoryInterface;

/**
 * Retourne la page demandée d'articles publiés.
 *
 * @phpstan-type ListResult array{items: list<ArticleSummaryView>, pagination: PaginationView}
 */
final class ListPublishedArticlesHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
    ) {
    }

    /**
     * @return array{items: list<ArticleSummaryView>, pagination: PaginationView}
     */
    public function __invoke(ListPublishedArticles $query): array
    {
        $total = $this->repository->countPublished();
        $pagination = PaginationView::fromCount($query->page, $query->perPage, $total);

        // Si la page demandée est au-delà du total, on renvoie une liste vide
        // sans lever d'erreur : conforme au reste de l'écosystème REST public
        // et évite de forcer le front à faire un pré-count.
        if ($total === 0 || $query->page > max(1, $pagination->totalPages)) {
            return [
                'items' => [],
                'pagination' => $pagination,
            ];
        }

        $items = array_map(
            static fn ($article): ArticleSummaryView => ArticleSummaryView::fromEntity($article),
            $this->repository->listPublished($query->page, $query->perPage),
        );

        return [
            'items' => array_values($items),
            'pagination' => $pagination,
        ];
    }
}
