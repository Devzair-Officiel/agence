<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application;

use App\Editorial\Application\Query\GetPublishedArticle;
use App\Editorial\Application\Query\GetPublishedArticleHandler;
use App\Editorial\Application\View\ArticleDetailView;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Infrastructure\Markdown\CommonMarkArticleRenderer;
use App\Editorial\Infrastructure\Markdown\MarkdownSecurityPolicy;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;

final class GetPublishedArticleHandlerTest extends TestCase
{
    public function testReturnsDetailViewForPublishedSlug(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('resource-publie')->published()->build());
        $handler = $this->handler($repository);

        $view = $handler(GetPublishedArticle::fromInput('resource-publie'));

        self::assertInstanceOf(ArticleDetailView::class, $view);
        self::assertSame('resource-publie', $view->slug);
        self::assertNotSame('', $view->bodyMarkdown);
    }

    public function testRendersContentHtmlFromMarkdownBody(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('article-html')->published()->build());
        $handler = $this->handler($repository);

        $view = $handler(GetPublishedArticle::fromInput('article-html'));

        // Le builder pose un corps commençant par "## Introduction\n\nCorps…".
        // Le rendu CommonMark doit produire un <h2> et au moins un <p>.
        self::assertStringContainsString('<h2>Introduction</h2>', $view->contentHtml);
        self::assertStringContainsString('<p>', $view->contentHtml);
    }

    public function testRaisesNotFoundForDraft(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('brouillon-invisible')->build());
        $handler = $this->handler($repository);

        $this->expectException(ArticleNotFoundException::class);

        $handler(GetPublishedArticle::fromInput('brouillon-invisible'));
    }

    public function testRaisesNotFoundForUnknownSlug(): void
    {
        $handler = $this->handler(new InMemoryArticleRepository());

        $this->expectException(ArticleNotFoundException::class);

        $handler(GetPublishedArticle::fromInput('slug-inexistant'));
    }

    public function testRaisesNotFoundWhenPublishedInFutureRelativeToClock(): void
    {
        $future = new \DateTimeImmutable('2027-01-01T00:00:00+00:00');
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())
                ->withSlug('article-programme')
                ->withNow($future)
                ->published()
                ->build(),
        );
        $handler = $this->handler($repository, new FixedClock('2026-08-04T09:00:00+00:00'));

        $this->expectException(ArticleNotFoundException::class);

        $handler(GetPublishedArticle::fromInput('article-programme'));
    }

    private function handler(
        InMemoryArticleRepository $repository,
        ?FixedClock $clock = null,
    ): GetPublishedArticleHandler {
        return new GetPublishedArticleHandler(
            $repository,
            $clock ?? new FixedClock(),
            new CommonMarkArticleRenderer(new MarkdownSecurityPolicy()),
        );
    }
}
