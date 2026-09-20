<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\DeleteArchivedArticle;
use App\Editorial\Application\Command\DeleteArchivedArticleHandler;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\Exception\ArticleNotDeletableException;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Couvre les règles métier du handler de suppression définitive.
 *
 * Tests :
 *   1. Article Draft → suppression refusée (`ArticleNotDeletableException`) ;
 *   2. Article Published → suppression refusée ;
 *   3. Article Archived → suppression autorisée + flush ;
 *   4. Après suppression, `findById()` renvoie null ;
 *   5. UUID inconnu → `ArticleNotFoundException`.
 */
final class DeleteArchivedArticleHandlerTest extends TestCase
{
    use EntityManagerStub;

    public function testRefusesDraftDeletion(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withId($id)->withSlug('draft-no-delete')->build());
        $handler = new DeleteArchivedArticleHandler($repository, $this->entityManagerExpectingNoFlush());

        $this->expectException(ArticleNotDeletableException::class);

        $handler(new DeleteArchivedArticle($id));
    }

    public function testRefusesPublishedDeletion(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())->withId($id)->withSlug('published-no-delete')->published()->build(),
        );
        $handler = new DeleteArchivedArticleHandler($repository, $this->entityManagerExpectingNoFlush());

        $this->expectException(ArticleNotDeletableException::class);

        $handler(new DeleteArchivedArticle($id));
    }

    public function testDeletesArchivedArticle(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $article = (new ArticleBuilder())->withId($id)->withSlug('archived-to-delete')->build();
        $article->archive(new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));
        $repository->save($article);

        $handler = new DeleteArchivedArticleHandler($repository, $this->entityManagerExpectingFlush());

        $handler(new DeleteArchivedArticle($id));

        self::assertNull($repository->findById($id), 'L\'article doit être absent du repository après suppression.');
    }

    public function testDeletesArchivedArticleThatWasPreviouslyPublished(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $article = (new ArticleBuilder())->withId($id)->withSlug('published-then-archived')->published()->build();
        $article->archive(new \DateTimeImmutable('2026-09-01T08:00:00+00:00'));
        $repository->save($article);

        $handler = new DeleteArchivedArticleHandler($repository, $this->entityManagerExpectingFlush());

        $handler(new DeleteArchivedArticle($id));

        self::assertNull($repository->findById($id));
    }

    public function testRaisesNotFoundForUnknownId(): void
    {
        $handler = new DeleteArchivedArticleHandler(
            new InMemoryArticleRepository(),
            $this->entityManagerExpectingNoFlush(),
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(new DeleteArchivedArticle(Uuid::v7()));
    }
}
