<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Domain;

use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Tests\Editorial\Support\ArticleBuilder;
use PHPUnit\Framework\TestCase;

final class ArticleTest extends TestCase
{
    public function testDraftHasNoPublishedAt(): void
    {
        $article = (new ArticleBuilder())->build();

        self::assertSame(ArticleStatus::Draft, $article->status());
        self::assertNull($article->publishedAt());
    }

    public function testPublishSetsStatusAndTimestamp(): void
    {
        $now = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->build();

        $article->publish($now);

        self::assertSame(ArticleStatus::Published, $article->status());
        self::assertNotNull($article->publishedAt());
        self::assertSame($now->getTimestamp(), $article->publishedAt()?->getTimestamp());
        self::assertTrue($article->status()->isPublished());
    }

    public function testPublishIsIdempotent(): void
    {
        $now = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->build();

        $article->publish($now);
        $firstPublishedAt = $article->publishedAt();

        $article->publish($now->modify('+1 day'));

        self::assertSame(
            $firstPublishedAt?->getTimestamp(),
            $article->publishedAt()?->getTimestamp(),
        );
    }

    public function testArchiveClearsExposedStatus(): void
    {
        $article = (new ArticleBuilder())->published()->build();
        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));

        self::assertSame(ArticleStatus::Archived, $article->status());
        self::assertFalse($article->status()->isPublished());
    }

    public function testArchiveKeepsPublishedAt(): void
    {
        $publishedAt = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->build();
        $article->publish($publishedAt);
        $capturedPublishedAt = $article->publishedAt();

        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));

        self::assertSame(ArticleStatus::Archived, $article->status());
        self::assertNotNull($article->publishedAt());
        self::assertSame(
            $capturedPublishedAt?->getTimestamp(),
            $article->publishedAt()?->getTimestamp(),
            'Un article archivé doit conserver sa date historique de publication.',
        );
    }

    public function testRequiresAtLeastOneExpertise(): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        (new ArticleBuilder())->withExpertises([])->build();
    }

    public function testDeduplicatesExpertises(): void
    {
        $article = (new ArticleBuilder())
            ->withExpertises([
                ExpertiseIdentifier::Concevoir,
                ExpertiseIdentifier::Concevoir,
                ExpertiseIdentifier::Valoriser,
            ])
            ->build();

        self::assertSame(
            [ExpertiseIdentifier::Concevoir, ExpertiseIdentifier::Valoriser],
            $article->expertises(),
        );
    }

    public function testRejectsEmptyBody(): void
    {
        $builder = new ArticleBuilder();
        $reflection = new \ReflectionProperty($builder, 'body');
        $reflection->setAccessible(true);
        $reflection->setValue($builder, '   ');

        $this->expectException(ArticleInvariantViolation::class);

        $builder->build();
    }

    public function testUpdatedAtEqualsCreatedAtOnConstruction(): void
    {
        $now = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($now)->build();

        self::assertSame(
            $article->createdAt()->getTimestamp(),
            $article->updatedAt()->getTimestamp(),
        );
    }

    public function testPublishBumpsUpdatedAt(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $published = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');

        $article = (new ArticleBuilder())->withNow($created)->build();
        $article->publish($published);

        self::assertSame($published->getTimestamp(), $article->updatedAt()->getTimestamp());
    }
}
