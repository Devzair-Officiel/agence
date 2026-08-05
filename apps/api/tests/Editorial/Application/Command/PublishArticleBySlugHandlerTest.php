<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\PublishArticleBySlug;
use App\Editorial\Application\Command\PublishArticleBySlugHandler;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;

final class PublishArticleBySlugHandlerTest extends TestCase
{
    use EntityManagerStub;

    public function testPublishesDraftAtClockNowByDefault(): void
    {
        $repository = new InMemoryArticleRepository();
        $article = (new ArticleBuilder())->withSlug('article-a-publier')->build();
        $repository->save($article);
        $clock = new FixedClock('2026-08-04T12:00:00+00:00');
        $handler = new PublishArticleBySlugHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            $clock,
        );

        $result = $handler(new PublishArticleBySlug(ArticleSlug::fromString('article-a-publier')));

        self::assertFalse($result->alreadyPublished);
        self::assertSame(ArticleStatus::Published, $result->article->status());
        self::assertEquals($clock->now(), $result->article->publishedAt());
    }

    public function testPublishesWithExplicitPastDate(): void
    {
        $repository = new InMemoryArticleRepository();
        $article = (new ArticleBuilder())->withSlug('article-antidate')->build();
        $repository->save($article);
        $publishedAt = new \DateTimeImmutable('2026-08-01T08:00:00+00:00');
        $handler = new PublishArticleBySlugHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-04T12:00:00+00:00'),
        );

        $result = $handler(new PublishArticleBySlug(
            ArticleSlug::fromString('article-antidate'),
            $publishedAt,
        ));

        self::assertEquals($publishedAt, $result->article->publishedAt());
    }

    public function testRefusesFuturePublishedAt(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('article-programme')->build());
        $handler = new PublishArticleBySlugHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-04T12:00:00+00:00'),
        );

        $this->expectException(ArticleInvariantViolation::class);
        $this->expectExceptionMessageMatches('/futur/u');

        $handler(new PublishArticleBySlug(
            ArticleSlug::fromString('article-programme'),
            new \DateTimeImmutable('2027-01-01T00:00:00+00:00'),
        ));
    }

    public function testRaisesNotFoundForUnknownSlug(): void
    {
        $repository = new InMemoryArticleRepository();
        $handler = new PublishArticleBySlugHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock(),
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(new PublishArticleBySlug(ArticleSlug::fromString('slug-inexistant')));
    }

    public function testIsIdempotentForAlreadyPublished(): void
    {
        $repository = new InMemoryArticleRepository();
        $article = (new ArticleBuilder())->withSlug('deja-publie')->published()->build();
        $originalPublishedAt = $article->publishedAt();
        $repository->save($article);
        $handler = new PublishArticleBySlugHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-12-01T00:00:00+00:00'),
        );

        $result = $handler(new PublishArticleBySlug(ArticleSlug::fromString('deja-publie')));

        self::assertTrue($result->alreadyPublished);
        self::assertEquals($originalPublishedAt, $result->article->publishedAt());
    }
}
