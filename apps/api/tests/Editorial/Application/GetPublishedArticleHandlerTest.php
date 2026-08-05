<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application;

use App\Editorial\Application\Query\GetPublishedArticle;
use App\Editorial\Application\Query\GetPublishedArticleHandler;
use App\Editorial\Application\View\ArticleDetailView;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;

final class GetPublishedArticleHandlerTest extends TestCase
{
    public function testReturnsDetailViewForPublishedSlug(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())
            ->withSlug('resource-publie')
            ->published()
            ->build());
        $handler = new GetPublishedArticleHandler($repository, new FixedClock());

        $view = $handler(GetPublishedArticle::fromInput('resource-publie'));

        self::assertInstanceOf(ArticleDetailView::class, $view);
        self::assertSame('resource-publie', $view->slug);
        self::assertNotSame('', $view->bodyMarkdown);
    }

    public function testRaisesNotFoundForDraft(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())
            ->withSlug('brouillon-invisible')
            ->build());
        $handler = new GetPublishedArticleHandler($repository, new FixedClock());

        $this->expectException(ArticleNotFoundException::class);

        $handler(GetPublishedArticle::fromInput('brouillon-invisible'));
    }

    public function testRaisesNotFoundForUnknownSlug(): void
    {
        $repository = new InMemoryArticleRepository();
        $handler = new GetPublishedArticleHandler($repository, new FixedClock());

        $this->expectException(ArticleNotFoundException::class);

        $handler(GetPublishedArticle::fromInput('slug-inexistant'));
    }

    public function testRaisesNotFoundWhenPublishedInFutureRelativeToClock(): void
    {
        $repository = new InMemoryArticleRepository();
        $future = new \DateTimeImmutable('2027-01-01T00:00:00+00:00');
        $repository->save((new ArticleBuilder())
            ->withSlug('article-programme')
            ->withNow($future)
            ->published()
            ->build());
        $handler = new GetPublishedArticleHandler(
            $repository,
            new FixedClock('2026-08-04T09:00:00+00:00'),
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(GetPublishedArticle::fromInput('article-programme'));
    }
}
