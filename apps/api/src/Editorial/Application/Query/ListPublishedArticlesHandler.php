<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Application\Media\MediaAssetReaderInterface;
use App\Editorial\Application\View\ArticleSummaryView;
use App\Editorial\Application\View\PaginationView;
use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\Clock\ClockInterface;

/**
 * Retourne la page demandée d'articles publiés (dont `publishedAt <= now`).
 *
 * Depuis la Phase 9B, le handler résout aussi le descripteur média pour
 * chaque article porteur d'image via `MediaAssetReaderInterface` (ACL
 * applicative). La résolution est faite ici — pas dans la présentation —
 * pour que la couche HTTP reste passive et que les tests d'ETag/JSON aient
 * les mêmes données que le contrat runtime.
 *
 * @phpstan-type ListResult array{items: list<ArticleSummaryView>, pagination: PaginationView}
 */
final class ListPublishedArticlesHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly ClockInterface $clock,
        private readonly MediaAssetReaderInterface $mediaReader,
    ) {
    }

    /**
     * @return array{items: list<ArticleSummaryView>, pagination: PaginationView}
     */
    public function __invoke(ListPublishedArticles $query): array
    {
        $now = $this->clock->now();
        $total = $this->repository->countPublished($now, $query->expertise);
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

        $articles = $this->repository->listPublished($query->page, $query->perPage, $now, $query->expertise);

        $items = array_map(
            fn (Article $article): ArticleSummaryView => ArticleSummaryView::fromEntity(
                $article,
                $this->resolveDescriptor($article),
            ),
            $articles,
        );

        return [
            'items' => array_values($items),
            'pagination' => $pagination,
        ];
    }

    private function resolveDescriptor(Article $article): ?\App\Editorial\Application\Media\MediaAssetDescriptor
    {
        $hero = $article->heroImage();

        return $hero === null ? null : $this->mediaReader->findMetadata($hero->mediaAssetId());
    }
}
