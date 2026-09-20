<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Couverture bout-à-bout du contrôleur de suppression définitive.
 *
 * Tests :
 *   1. Anonyme → redirection login ;
 *   2. Accès GET → page de confirmation (200) pour article archivé ;
 *   3. Article non archivé (Draft) → GET affiche la page mais POST refuse ;
 *   4. Article Archived → POST sans CSRF → redirige sans supprimer ;
 *   5. Article Archived → POST avec CSRF valide → suppression + liste ;
 *   6. Après suppression, findById() renvoie null ;
 *   7. UUID inconnu → 404 ;
 *   8. Article Published → POST refuse (409 via flash) ;
 *   9. POST uniquement pour supprimer — GET ne déclenche jamais la suppression.
 */
final class AdminArticleDeleteControllerTest extends WebTestCase
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

    public function testAnonymousRedirectsToLogin(): void
    {
        $article = $this->seedArchived('anon-delete');
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/delete');

        self::assertResponseRedirects();
        self::assertStringContainsString('/admin/login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testGetShowsConfirmationPageForArchivedArticle(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedArchived('get-confirm-page');
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/delete');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Supprimer définitivement', (string) $this->client->getResponse()->getContent());
    }

    public function testGetDoesNotDeleteArticle(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedArchived('get-no-delete');
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/delete');

        self::assertResponseIsSuccessful();
        self::assertNotNull($this->tryReloadArticle($id), 'Un GET ne doit jamais supprimer l\'article.');
    }

    public function testPostWithInvalidCsrfRedirectsWithoutDeleting(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedArchived('csrf-invalid-delete');
        $id = $article->id()->toRfc4122();

        $this->client->request('POST', '/admin/articles/'.$id.'/delete', ['_csrf_token' => 'invalid']);

        self::assertResponseRedirects();
        self::assertNotNull($this->tryReloadArticle($id), 'Un CSRF invalide ne doit pas supprimer l\'article.');
    }

    public function testDeleteArchivedArticleSucceeds(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedArchived('delete-happy-path');
        $id = $article->id()->toRfc4122();

        $token = $this->csrfTokenFromConfirmPage($id);
        $this->client->request('POST', '/admin/articles/'.$id.'/delete', ['_csrf_token' => $token]);

        self::assertResponseRedirects('/admin/articles');
        self::assertNull($this->tryReloadArticle($id), 'L\'article doit être absent après suppression définitive.');
    }

    public function testDeleteDraftArticleIsRefused(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('draft-no-delete');
        $id = $article->id()->toRfc4122();

        // On obtient un token via la page de confirmation (affichée même pour Draft,
        // mais la soumission doit être refusée par le handler).
        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/delete');
        $token = $crawler->filter('input[name="_csrf_token"]')->attr('value') ?? '';

        $this->client->request('POST', '/admin/articles/'.$id.'/delete', ['_csrf_token' => $token]);

        self::assertResponseRedirects();
        self::assertNotNull($this->tryReloadArticle($id), 'Un article Draft ne doit pas être supprimable.');
    }

    public function testDeletePublishedArticleIsRefused(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article = (new ArticleBuilder())->withSlug('published-no-delete')->published()->build();
        $repo->save($article);
        $em->flush();
        $id = $article->id()->toRfc4122();

        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/delete');
        $token = $crawler->filter('input[name="_csrf_token"]')->attr('value') ?? '';

        $this->client->request('POST', '/admin/articles/'.$id.'/delete', ['_csrf_token' => $token]);

        self::assertResponseRedirects();
        self::assertNotNull($this->tryReloadArticle($id), 'Un article Published ne doit pas être supprimable.');
    }

    public function testUnknownIdReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $unknownId = Uuid::v7()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$unknownId.'/delete');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeletedArticleIsAbsentFromRepository(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedArchived('deleted-absent');
        $id = $article->id()->toRfc4122();

        $token = $this->csrfTokenFromConfirmPage($id);
        $this->client->request('POST', '/admin/articles/'.$id.'/delete', ['_csrf_token' => $token]);

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        self::assertNull(
            $repo->findById(Uuid::fromString($id)),
            'findById() doit renvoyer null après suppression définitive.',
        );
    }

    private function seedDraft(string $slug): Article
    {
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article = (new ArticleBuilder())->withSlug($slug)->build();
        $repo->save($article);
        $em->flush();

        return $article;
    }

    private function seedArchived(string $slug): Article
    {
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article = (new ArticleBuilder())->withSlug($slug)->build();
        $article->archive(new \DateTimeImmutable('2026-09-01T10:00:00+00:00'));
        $repo->save($article);
        $em->flush();

        return $article;
    }

    private function csrfTokenFromConfirmPage(string $id): string
    {
        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/delete');
        $xpath = \sprintf(
            '//form[contains(@action, "/admin/articles/%s/delete")]//input[@name="_csrf_token"]',
            $id,
        );
        $node = $crawler->filterXPath($xpath);
        \assert($node->count() > 0, \sprintf('Champ CSRF introuvable sur la page de confirmation pour %s.', $id));
        $token = $node->attr('value');
        \assert(\is_string($token) && $token !== '');

        return $token;
    }

    private function tryReloadArticle(string $id): ?Article
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);

        return $repo->findById(Uuid::fromString($id));
    }
}
