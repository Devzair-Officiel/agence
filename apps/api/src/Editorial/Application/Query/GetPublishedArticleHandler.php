<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Application\View\ArticleDetailView;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Infrastructure\Markdown\CommonMarkArticleRenderer;

/**
 * Charge un article publié, rend son corps Markdown en HTML sécurisé et
 * construit la vue détail. Le rendu est fait ici (couche Application) pour
 * concentrer la conversion Markdown → HTML dans une seule frontière de
 * confiance côté Symfony (ADR-011). Le repository/domaine ignorent tout
 * du pipeline HTML.
 */
final class GetPublishedArticleHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly ClockInterface $clock,
        private readonly CommonMarkArticleRenderer $renderer,
    ) {
    }

    public function __invoke(GetPublishedArticle $query): ArticleDetailView
    {
        $article = $this->repository->getPublishedBySlug($query->slug, $this->clock->now());

        return ArticleDetailView::fromEntity(
            $article,
            $this->renderer->renderHtml($article->bodyMarkdown()),
        );
    }
}
