<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\RestoreArticle;
use App\Editorial\Application\Command\RestoreArticleHandler;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\Exception\InvalidArticleTransitionException;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class RestoreArticleHandlerTest extends TestCase
{
    use EntityManagerStub;

    public function testRestoresArchivedArticleToDraft(): void
    {
        $id = Uuid::v7();
        $article = (new ArticleBuilder())->withId($id)->withSlug('a-restaurer')->published()->build();
        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));
        $repository = new InMemoryArticleRepository();
        $repository->save($article);
        $handler = new RestoreArticleHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-06T00:00:00+00:00'),
        );

        $result = $handler(new RestoreArticle($id));

        self::assertFalse($result->alreadyDraft);
        self::assertSame(ArticleStatus::Draft, $result->article->status());
        self::assertNull(
            $result->article->publishedAt(),
            'Un article restauré perd sa date de publication.',
        );
    }

    public function testIsIdempotentForDraft(): void
    {
        $id = Uuid::v7();
        $article = (new ArticleBuilder())->withId($id)->withSlug('deja-draft')->build();
        $originalUpdatedAt = $article->updatedAt();
        $repository = new InMemoryArticleRepository();
        $repository->save($article);
        $handler = new RestoreArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-12-01T00:00:00+00:00'),
        );

        $result = $handler(new RestoreArticle($id));

        self::assertTrue($result->alreadyDraft);
        self::assertSame(
            $originalUpdatedAt->getTimestamp(),
            $result->article->updatedAt()->getTimestamp(),
        );
    }

    public function testRefusesRestoreOnPublishedArticle(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())->withId($id)->withSlug('article-publie')->published()->build(),
        );
        $handler = new RestoreArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-12-01T00:00:00+00:00'),
        );

        $this->expectException(InvalidArticleTransitionException::class);

        $handler(new RestoreArticle($id));
    }

    public function testRaisesNotFoundForUnknownId(): void
    {
        $handler = new RestoreArticleHandler(
            new InMemoryArticleRepository(),
            $this->entityManagerExpectingNoFlush(),
            new FixedClock(),
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(new RestoreArticle(Uuid::v7()));
    }
}
