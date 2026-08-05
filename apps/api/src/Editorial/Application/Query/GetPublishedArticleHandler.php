<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Application\View\ArticleDetailView;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\Clock\ClockInterface;

final class GetPublishedArticleHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(GetPublishedArticle $query): ArticleDetailView
    {
        $article = $this->repository->getPublishedBySlug($query->slug, $this->clock->now());

        return ArticleDetailView::fromEntity($article);
    }
}
