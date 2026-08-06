<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\ArchiveArticle;
use App\Editorial\Application\Command\ArchiveArticleHandler;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ArchiveArticleHandlerTest extends TestCase
{
    use EntityManagerStub;

    public function testArchivesPublishedArticle(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())->withId($id)->withSlug('a-archiver')->published()->build(),
        );
        $handler = new ArchiveArticleHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T12:00:00+00:00'),
        );

        $result = $handler(new ArchiveArticle($id));

        self::assertFalse($result->alreadyArchived);
        self::assertSame(ArticleStatus::Archived, $result->article->status());
        self::assertNotNull(
            $result->article->publishedAt(),
            'L\'archivage doit conserver la date historique de publication.',
        );
    }

    public function testArchivesDraftArticle(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withId($id)->withSlug('draft-archive')->build());
        $handler = new ArchiveArticleHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T12:00:00+00:00'),
        );

        $result = $handler(new ArchiveArticle($id));

        self::assertSame(ArticleStatus::Archived, $result->article->status());
        self::assertNull(
            $result->article->publishedAt(),
            'Un brouillon archivé n\'a jamais eu de publishedAt.',
        );
    }

    public function testIsIdempotentForAlreadyArchived(): void
    {
        $id = Uuid::v7();
        $article = (new ArticleBuilder())->withId($id)->withSlug('deja-archive')->published()->build();
        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));
        $originalUpdatedAt = $article->updatedAt();
        $repository = new InMemoryArticleRepository();
        $repository->save($article);
        $handler = new ArchiveArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-12-01T00:00:00+00:00'),
        );

        $result = $handler(new ArchiveArticle($id));

        self::assertTrue($result->alreadyArchived);
        self::assertSame(
            $originalUpdatedAt->getTimestamp(),
            $result->article->updatedAt()->getTimestamp(),
        );
    }

    public function testRaisesNotFoundForUnknownId(): void
    {
        $handler = new ArchiveArticleHandler(
            new InMemoryArticleRepository(),
            $this->entityManagerExpectingNoFlush(),
            new FixedClock(),
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(new ArchiveArticle(Uuid::v7()));
    }
}
