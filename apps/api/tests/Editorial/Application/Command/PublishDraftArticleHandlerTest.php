<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\PublishDraftArticle;
use App\Editorial\Application\Command\PublishDraftArticleHandler;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\Exception\InvalidArticleTransitionException;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class PublishDraftArticleHandlerTest extends TestCase
{
    use EntityManagerStub;

    public function testPublishesDraftAtNow(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withId($id)->withSlug('a-publier')->build());
        $handler = new PublishDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T14:00:00+00:00'),
        );

        $result = $handler(new PublishDraftArticle($id));

        self::assertSame(ArticleStatus::Published, $result->article->status());
        self::assertNotNull($result->article->publishedAt());
        self::assertSame(
            '2026-08-05T14:00:00+00:00',
            $result->article->publishedAt()?->format(\DateTimeInterface::ATOM),
        );
    }

    public function testRefusesFromArchivedRestoreFirst(): void
    {
        $id = Uuid::v7();
        $article = (new ArticleBuilder())->withId($id)->withSlug('archive-repub')->published()->build();
        $article->archive(new \DateTimeImmutable('2026-08-04T10:00:00+00:00'));
        $repository = new InMemoryArticleRepository();
        $repository->save($article);
        $handler = new PublishDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T14:00:00+00:00'),
        );

        $this->expectException(InvalidArticleTransitionException::class);
        $this->expectExceptionMessageMatches('/à partir du statut "draft".*archived/');

        $handler(new PublishDraftArticle($id));
    }

    public function testRefusesFromAlreadyPublished(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withId($id)->withSlug('deja-publie')->published()->build());
        $handler = new PublishDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T14:00:00+00:00'),
        );

        $this->expectException(InvalidArticleTransitionException::class);
        $this->expectExceptionMessageMatches('/statut "draft".*published/');

        $handler(new PublishDraftArticle($id));
    }

    public function testRaisesNotFoundForUnknownId(): void
    {
        $handler = new PublishDraftArticleHandler(
            new InMemoryArticleRepository(),
            $this->entityManagerExpectingNoFlush(),
            new FixedClock(),
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(new PublishDraftArticle(Uuid::v7()));
    }
}
