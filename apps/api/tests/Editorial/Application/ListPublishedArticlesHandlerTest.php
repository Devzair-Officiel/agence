<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application;

use App\Editorial\Application\Query\ListPublishedArticles;
use App\Editorial\Application\Query\ListPublishedArticlesHandler;
use App\Editorial\Application\View\ArticleSummaryView;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ListPublishedArticlesHandlerTest extends TestCase
{
    private InMemoryArticleRepository $repository;

    private ListPublishedArticlesHandler $handler;

    protected function setUp(): void
    {
        $this->repository = new InMemoryArticleRepository();
        $this->handler = new ListPublishedArticlesHandler($this->repository);
    }

    public function testReturnsEmptyWhenNoArticle(): void
    {
        $result = ($this->handler)(ListPublishedArticles::fromInputs(1, null));

        self::assertSame([], $result['items']);
        self::assertSame(0, $result['pagination']->total);
        self::assertSame(0, $result['pagination']->totalPages);
    }

    public function testFiltersOutDrafts(): void
    {
        $this->repository->save((new ArticleBuilder())
            ->withSlug('draft-only')
            ->build());
        $this->repository->save((new ArticleBuilder())
            ->withSlug('published-one')
            ->published()
            ->build());

        $result = ($this->handler)(ListPublishedArticles::fromInputs(1, null));

        self::assertCount(1, $result['items']);
        self::assertSame('published-one', $result['items'][0]->slug);
    }

    public function testOrdersByPublishedAtDescendingThenById(): void
    {
        $baseline = new \DateTimeImmutable('2026-08-01T10:00:00+00:00');

        // Deux articles publiés à la même seconde → tri stable par id DESC.
        $olderIdHigher = Uuid::fromString('01925000-0000-7000-8000-000000000002');
        $olderIdLower = Uuid::fromString('01925000-0000-7000-8000-000000000001');

        $this->repository->save((new ArticleBuilder())
            ->withId($olderIdLower)
            ->withSlug('article-plus-ancien-un')
            ->withNow($baseline)
            ->published()
            ->build());
        $this->repository->save((new ArticleBuilder())
            ->withId($olderIdHigher)
            ->withSlug('article-plus-ancien-deux')
            ->withNow($baseline)
            ->published()
            ->build());
        $this->repository->save((new ArticleBuilder())
            ->withSlug('article-plus-recent')
            ->withNow($baseline->modify('+2 days'))
            ->published()
            ->build());

        $result = ($this->handler)(ListPublishedArticles::fromInputs(1, null));

        self::assertCount(3, $result['items']);
        self::assertSame('article-plus-recent', $result['items'][0]->slug);
        // À égalité de publishedAt, id le plus grand vient en premier.
        self::assertSame('article-plus-ancien-deux', $result['items'][1]->slug);
        self::assertSame('article-plus-ancien-un', $result['items'][2]->slug);
    }

    public function testPaginationHonorsPageAndPerPage(): void
    {
        for ($i = 0; $i < 15; ++$i) {
            $this->repository->save((new ArticleBuilder())
                ->withSlug(\sprintf('article-numero-%02d', $i))
                ->withNow(new \DateTimeImmutable(\sprintf('2026-08-%02dT10:00:00+00:00', $i + 1)))
                ->published()
                ->build());
        }

        $page2 = ($this->handler)(ListPublishedArticles::fromInputs(2, 5));

        self::assertCount(5, $page2['items']);
        self::assertSame(15, $page2['pagination']->total);
        self::assertSame(3, $page2['pagination']->totalPages);
        self::assertSame(5, $page2['pagination']->perPage);
    }

    public function testReturnsEmptyPageBeyondTotal(): void
    {
        $this->repository->save((new ArticleBuilder())->published()->build());

        $result = ($this->handler)(ListPublishedArticles::fromInputs(42, 10));

        self::assertSame([], $result['items']);
        self::assertSame(1, $result['pagination']->total);
    }

    public function testItemsAreArticleSummaryViews(): void
    {
        $this->repository->save((new ArticleBuilder())->published()->build());

        $result = ($this->handler)(ListPublishedArticles::fromInputs(1, null));

        self::assertInstanceOf(ArticleSummaryView::class, $result['items'][0]);
    }

    public function testFromInputsRejectsInvalidPage(): void
    {
        $this->expectException(ArticleInvariantViolation::class);
        ListPublishedArticles::fromInputs(0, null);
    }

    public function testFromInputsRejectsPerPageAboveMax(): void
    {
        $this->expectException(ArticleInvariantViolation::class);
        ListPublishedArticles::fromInputs(1, 51);
    }
}
