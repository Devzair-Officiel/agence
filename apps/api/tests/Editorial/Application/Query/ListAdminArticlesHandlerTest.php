<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Query;

use App\Editorial\Application\Query\ListAdminArticles;
use App\Editorial\Application\Query\ListAdminArticlesHandler;
use App\Editorial\Domain\ArticleStatus;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\InMemoryAdminArticleReadRepository;
use PHPUnit\Framework\TestCase;

final class ListAdminArticlesHandlerTest extends TestCase
{
    public function testReturnsAllStatusesOrderedByUpdatedAtDesc(): void
    {
        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save((new ArticleBuilder())
            ->withSlug('draft-recent')
            ->withNow(new \DateTimeImmutable('2026-08-05T10:00:00+00:00'))
            ->build());
        $repo->save((new ArticleBuilder())
            ->withSlug('draft-ancien')
            ->withNow(new \DateTimeImmutable('2026-08-01T10:00:00+00:00'))
            ->build());
        $repo->save((new ArticleBuilder())
            ->withSlug('publie')
            ->withNow(new \DateTimeImmutable('2026-08-03T10:00:00+00:00'))
            ->published()
            ->build());

        $handler = new ListAdminArticlesHandler($repo);
        $page = $handler(new ListAdminArticles(1, 10, null));

        self::assertSame(3, $page->total);
        self::assertCount(3, $page->items);
        self::assertSame('draft-recent', $page->items[0]->slug);
        self::assertSame('publie', $page->items[1]->slug);
        self::assertSame('draft-ancien', $page->items[2]->slug);
    }

    public function testFiltersByStatus(): void
    {
        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save((new ArticleBuilder())->withSlug('brouillon-1')->build());
        $repo->save((new ArticleBuilder())->withSlug('brouillon-2')->build());
        $repo->save((new ArticleBuilder())->withSlug('publie-1')->published()->build());

        $handler = new ListAdminArticlesHandler($repo);
        $page = $handler(new ListAdminArticles(1, 10, ArticleStatus::Published));

        self::assertSame(1, $page->total);
        self::assertCount(1, $page->items);
        self::assertSame('publie-1', $page->items[0]->slug);
        self::assertSame(ArticleStatus::Published, $page->statusFilter);
    }

    public function testPaginationBoundaries(): void
    {
        $repo = new InMemoryAdminArticleReadRepository();
        for ($i = 1; $i <= 5; ++$i) {
            $repo->save((new ArticleBuilder())
                ->withSlug('article-'.$i)
                ->withNow(new \DateTimeImmutable(\sprintf('2026-08-0%dT10:00:00+00:00', $i)))
                ->build());
        }

        $handler = new ListAdminArticlesHandler($repo);
        $page = $handler(new ListAdminArticles(2, 2, null));

        self::assertSame(5, $page->total);
        self::assertCount(2, $page->items);
        self::assertSame(3, $page->lastPage());
        self::assertTrue($page->hasPrevious());
        self::assertTrue($page->hasNext());
    }

    public function testEmptyRepositoryStillHasLastPageOne(): void
    {
        $handler = new ListAdminArticlesHandler(new InMemoryAdminArticleReadRepository());
        $page = $handler(new ListAdminArticles(1, 20, null));

        self::assertSame(0, $page->total);
        self::assertSame(1, $page->lastPage());
        self::assertFalse($page->hasPrevious());
        self::assertFalse($page->hasNext());
    }
}
