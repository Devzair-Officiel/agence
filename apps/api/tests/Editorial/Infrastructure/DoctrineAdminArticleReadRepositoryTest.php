<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Infrastructure;

use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Infrastructure\Persistence\DoctrineAdminArticleReadRepository;
use App\Editorial\Infrastructure\Persistence\DoctrineArticleRepository;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Vérifie que l'adaptateur Doctrine du port admin retourne bien tous les
 * statuts (contrairement au port public), applique le tri
 * `updatedAt DESC, id DESC`, et respecte le filtre optionnel.
 */
final class DoctrineAdminArticleReadRepositoryTest extends KernelTestCase
{
    use EditorialDatabaseCleanup;

    private EntityManagerInterface $entityManager;

    private DoctrineArticleRepository $writeRepository;

    private DoctrineAdminArticleReadRepository $adminRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->writeRepository = $container->get(DoctrineArticleRepository::class);
        $this->adminRepository = $container->get(DoctrineAdminArticleReadRepository::class);

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

    public function testReturnsAllStatusesUnfiltered(): void
    {
        $this->writeRepository->save((new ArticleBuilder())->withSlug('adm-draft')->build());
        $this->writeRepository->save((new ArticleBuilder())->withSlug('adm-publie')->published()->build());
        $this->entityManager->flush();

        self::assertSame(2, $this->adminRepository->count(null));
        self::assertCount(2, $this->adminRepository->paginate(1, 10, null));
    }

    public function testFiltersByStatus(): void
    {
        $this->writeRepository->save((new ArticleBuilder())->withSlug('adm-flt-draft')->build());
        $this->writeRepository->save((new ArticleBuilder())->withSlug('adm-flt-pub')->published()->build());
        $this->entityManager->flush();

        self::assertSame(1, $this->adminRepository->count(ArticleStatus::Draft));
        $drafts = $this->adminRepository->paginate(1, 10, ArticleStatus::Draft);
        self::assertCount(1, $drafts);
        self::assertSame('adm-flt-draft', $drafts[0]->slug);
    }

    public function testFindForEditReturnsPlainView(): void
    {
        $article = (new ArticleBuilder())->withSlug('adm-vue-edit')->build();
        $this->writeRepository->save($article);
        $this->entityManager->flush();

        $view = $this->adminRepository->findForEdit($article->id());

        self::assertNotNull($view);
        self::assertSame('adm-vue-edit', $view->slug);
    }

    public function testFindForEditReturnsNullWhenAbsent(): void
    {
        self::assertNull($this->adminRepository->findForEdit(\Symfony\Component\Uid\Uuid::v7()));
    }
}
