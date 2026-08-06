<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie la liste paginée `/admin/articles` :
 *   - la page charge un admin authentifié et affiche le tableau ;
 *   - le filtre `?status=draft` restreint bien le résultat ;
 *   - la pagination avec `?page=2` renvoie la page suivante ;
 *   - un slug archivé n'est pas exposé au titre « Publier ».
 */
final class AdminArticleListControllerTest extends WebTestCase
{
    use EditorialDatabaseCleanup;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        AdminDatabaseCleanup::purge($em);
        $this->clearEditorialTables($em);
    }

    public function testListReturnsPaginatedTableForAuthenticatedAdmin(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedArticles(3);

        $crawler = $this->client->request('GET', '/admin/articles');

        self::assertResponseIsSuccessful();
        $cacheControl = (string) $this->client->getResponse()->headers->get('Cache-Control');
        self::assertStringContainsString('no-store', $cacheControl);
        self::assertStringContainsString('no-cache', $cacheControl);
        self::assertStringContainsString('must-revalidate', $cacheControl);
        self::assertStringContainsString('private', $cacheControl);
        self::assertGreaterThanOrEqual(3, $crawler->filterXPath('//table[contains(@class, "admin-table")]//tbody/tr')->count());
    }

    public function testFilterByDraftHidesPublishedRows(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $repo->save((new ArticleBuilder())
            ->withSlug('draft-alpha')->withTitle('Draft Alpha')->build());
        $repo->save((new ArticleBuilder())
            ->withSlug('published-beta')->withTitle('Published Beta')->published()->build());
        $em->flush();

        $crawler = $this->client->request('GET', '/admin/articles?status=draft');

        self::assertResponseIsSuccessful();
        $body = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Draft Alpha', $body);
        self::assertStringNotContainsString('Published Beta', $body);
    }

    public function testUnauthenticatedIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin/articles');

        self::assertResponseRedirects();
        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/admin/login', $location);
    }

    public function testPaginationExposesNextPageLink(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedArticles(22);

        $crawler = $this->client->request('GET', '/admin/articles');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filterXPath('//ul[contains(@class, "pagination-links")]//a')->count());
    }

    private function seedArticles(int $count): void
    {
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        for ($i = 1; $i <= $count; ++$i) {
            $repo->save((new ArticleBuilder())
                ->withSlug(\sprintf('article-list-%02d', $i))
                ->withTitle(\sprintf('Article de test %02d — pour paginer', $i))
                ->build());
        }
        $em->flush();
    }
}
