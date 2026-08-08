<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Query;

use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;

/**
 * Handler minimal — délègue au repository et emballe le résultat.
 * On garde un handler explicite pour uniformiser les points d'entrée
 * applicatifs (test/wire depuis les contrôleurs) et pouvoir décorer plus tard.
 */
final class ListAdminMediaHandler
{
    public function __construct(
        private readonly MediaAssetRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ListAdminMedia $query): AdminMediaListPage
    {
        $assets = $this->repository->list($query->page, $query->perPage);
        $items = array_map(
            static fn ($asset) => AdminMediaListItem::fromEntity($asset),
            $assets,
        );
        $total = $this->repository->count();

        return new AdminMediaListPage(
            items: $items,
            page: $query->page,
            perPage: $query->perPage,
            total: $total,
        );
    }
}
