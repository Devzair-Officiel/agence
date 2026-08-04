<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Application\View\ArticleDetailView;
use App\Editorial\Domain\ArticleRepositoryInterface;

final class GetPublishedArticleHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(GetPublishedArticle $query): ArticleDetailView
    {
        $article = $this->repository->getPublishedBySlug($query->slug);

        return ArticleDetailView::fromEntity($article);
    }
}
