<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Infrastructure;

use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Infrastructure\Persistence\DoctrineArticleRepository;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test d'intégration Doctrine — nécessite `devzair_test` accessible.
 *
 * Chaque test ouvre une transaction dans `setUp` et la rollback dans
 * `tearDown` : la base reste inchangée d'un test à l'autre, y compris en
 * exécution parallèle (via PHPUnit 11 default order = random).
 */
final class DoctrineArticleRepositoryTest extends KernelTestCase
{
    use EditorialDatabaseCleanup;

    private EntityManagerInterface $entityManager;

    private DoctrineArticleRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(DoctrineArticleRepository::class);

        $this->clearEditorialTables($this->entityManager);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
        $this->entityManager->close();
    }

    public function testSaveAndReadPublishedArticle(): void
    {
        $article = (new ArticleBuilder())
            ->withSlug('article-persiste-en-base')
            ->published()
            ->build();

        $this->repository->save($article);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $found = $this->repository->getPublishedBySlug(
            ArticleSlug::fromString('article-persiste-en-base'),
        );

        self::assertSame(ArticleStatus::Published, $found->status());
        self::assertSame('article-persiste-en-base', $found->slug()->value());
    }

    public function testGetPublishedBySlugRaisesForDraft(): void
    {
        $article = (new ArticleBuilder())
            ->withSlug('brouillon-en-base')
            ->build();

        $this->repository->save($article);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->expectException(ArticleNotFoundException::class);

        $this->repository->getPublishedBySlug(
            ArticleSlug::fromString('brouillon-en-base'),
        );
    }

    public function testListPublishedIgnoresDraftsAndArchives(): void
    {
        $draft = (new ArticleBuilder())->withSlug('draft-x')->build();
        $published = (new ArticleBuilder())->withSlug('published-x')->published()->build();
        $archived = (new ArticleBuilder())->withSlug('archived-x')->published()->build();
        $archived->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));

        $this->repository->save($draft);
        $this->repository->save($published);
        $this->repository->save($archived);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $items = $this->repository->listPublished(1, 10);

        self::assertCount(1, $items);
        self::assertSame('published-x', $items[0]->slug()->value());
        self::assertSame(1, $this->repository->countPublished());
    }
}
