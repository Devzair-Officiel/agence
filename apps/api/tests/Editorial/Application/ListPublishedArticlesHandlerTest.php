<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application;

use App\Editorial\Application\Query\ListPublishedArticles;
use App\Editorial\Application\Query\ListPublishedArticlesHandler;
use App\Editorial\Application\View\ArticleSummaryView;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use App\Tests\Editorial\Support\InMemoryMediaAssetLookup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ListPublishedArticlesHandlerTest extends TestCase
{
    private InMemoryArticleRepository $repository;

    private FixedClock $clock;

    private InMemoryMediaAssetLookup $mediaLookup;

    private ListPublishedArticlesHandler $handler;

    protected function setUp(): void
    {
        $this->repository = new InMemoryArticleRepository();
        $this->clock = new FixedClock('2026-09-01T00:00:00+00:00');
        $this->mediaLookup = new InMemoryMediaAssetLookup();
        $this->handler = new ListPublishedArticlesHandler($this->repository, $this->clock, $this->mediaLookup);
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

    public function testFiltersOutFuturePublications(): void
    {
        $this->repository->save((new ArticleBuilder())
            ->withSlug('present')
            ->withNow(new \DateTimeImmutable('2026-08-01T10:00:00+00:00'))
            ->published()
            ->build());
        $this->repository->save((new ArticleBuilder())
            ->withSlug('future-scheduled')
            ->withNow(new \DateTimeImmutable('2027-01-01T00:00:00+00:00'))
            ->published()
            ->build());

        $result = ($this->handler)(ListPublishedArticles::fromInputs(1, null));

        self::assertCount(1, $result['items']);
        self::assertSame('present', $result['items'][0]->slug);
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

    public function testFromInputsAcceptsAllExpertiseCases(): void
    {
        foreach (ExpertiseIdentifier::cases() as $case) {
            $query = ListPublishedArticles::fromInputs(1, null, $case->value);
            self::assertSame($case, $query->expertise);
        }
    }

    public function testFromInputsRejectsUnknownExpertise(): void
    {
        $this->expectException(ArticleInvariantViolation::class);
        ListPublishedArticles::fromInputs(1, null, 'crypto-monnaie');
    }

    public function testFromInputsWithEmptyExpertiseIsUnfiltered(): void
    {
        $query = ListPublishedArticles::fromInputs(1, null, '');
        self::assertNull($query->expertise);
    }

    public function testFiltersByExpertiseWhenProvided(): void
    {
        $this->repository->save((new ArticleBuilder())
            ->withSlug('article-concevoir')
            ->withExpertises([ExpertiseIdentifier::Concevoir])
            ->published()
            ->build());
        $this->repository->save((new ArticleBuilder())
            ->withSlug('article-construire')
            ->withExpertises([ExpertiseIdentifier::Construire])
            ->published()
            ->build());
        $this->repository->save((new ArticleBuilder())
            ->withSlug('article-multi')
            ->withExpertises([ExpertiseIdentifier::Concevoir, ExpertiseIdentifier::Visibilite])
            ->published()
            ->build());

        $result = ($this->handler)(
            ListPublishedArticles::fromInputs(1, null, ExpertiseIdentifier::Concevoir->value),
        );

        self::assertSame(2, $result['pagination']->total);
        $slugs = array_map(
            static fn (ArticleSummaryView $view): string => $view->slug,
            $result['items'],
        );
        self::assertContains('article-concevoir', $slugs);
        self::assertContains('article-multi', $slugs);
        self::assertNotContains('article-construire', $slugs);
    }

    public function testExpertiseFilterExcludesDraftsAndFuturePublications(): void
    {
        $this->repository->save((new ArticleBuilder())
            ->withSlug('draft-concevoir')
            ->withExpertises([ExpertiseIdentifier::Concevoir])
            ->build());
        $this->repository->save((new ArticleBuilder())
            ->withSlug('future-concevoir')
            ->withExpertises([ExpertiseIdentifier::Concevoir])
            ->withNow(new \DateTimeImmutable('2027-01-01T00:00:00+00:00'))
            ->published()
            ->build());
        $this->repository->save((new ArticleBuilder())
            ->withSlug('published-concevoir')
            ->withExpertises([ExpertiseIdentifier::Concevoir])
            ->published()
            ->build());

        $result = ($this->handler)(
            ListPublishedArticles::fromInputs(1, null, ExpertiseIdentifier::Concevoir->value),
        );

        self::assertCount(1, $result['items']);
        self::assertSame('published-concevoir', $result['items'][0]->slug);
    }

    public function testExpertiseFilterCombinesWithPagination(): void
    {
        for ($i = 0; $i < 12; ++$i) {
            $this->repository->save((new ArticleBuilder())
                ->withSlug(\sprintf('valoriser-%02d', $i))
                ->withExpertises([ExpertiseIdentifier::Valoriser])
                ->withNow(new \DateTimeImmutable(\sprintf('2026-08-%02dT10:00:00+00:00', $i + 1)))
                ->published()
                ->build());
        }
        $this->repository->save((new ArticleBuilder())
            ->withSlug('autre-expertise')
            ->withExpertises([ExpertiseIdentifier::Construire])
            ->published()
            ->build());

        $page2 = ($this->handler)(
            ListPublishedArticles::fromInputs(2, 5, ExpertiseIdentifier::Valoriser->value),
        );

        self::assertCount(5, $page2['items']);
        self::assertSame(12, $page2['pagination']->total);
        self::assertSame(3, $page2['pagination']->totalPages);
        foreach ($page2['items'] as $item) {
            self::assertContains('valoriser', $item->expertiseIds);
        }
    }
}
