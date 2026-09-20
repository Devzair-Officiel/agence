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
 * Défense en profondeur :
 *   - Contrôleur (UI) : refuse GET et POST si l'article n'est pas Archived
 *     (redirect 303 vers édition + flash error).
 *   - Handler (domaine) : refuse toujours la suppression si l'article n'est pas
 *     Archived, indépendamment de ce que l'UI vérifie.
 *     → invariant couvert par DeleteArchivedArticleHandlerTest (unitaire).
 *
 * Tests HTTP :
 *   1.  Anonyme → redirection login.
 *   2.  Article Archived → GET affiche la page de confirmation (200).
 *   3.  Article Archived → GET ne déclenche pas la suppression.
 *   4.  Article Archived → POST sans CSRF → 303 sans supprimer.
 *   5.  Article Archived → POST avec CSRF valide → suppression + liste.
 *   6.  Après suppression, findById() renvoie null.
 *   7.  UUID inconnu → 404.
 *   8.  Article Draft → GET redirige vers édition (pas de confirmation).
 *   9.  Article Published → GET redirige vers édition (pas de confirmation).
 *   10. Article Draft → POST redirige sans supprimer (garde contrôleur).
 *   11. Article Published → POST redirige sans supprimer (garde contrôleur).
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

    // ── Garde contrôleur : articles non archivés ──────────────────────────

    public function testGetDraftArticleRedirectsToEdit(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('draft-get-guard');
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/delete');

        self::assertResponseRedirects();
        self::assertStringContainsString('/admin/articles/'.$id.'/edit', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testGetPublishedArticleRedirectsToEdit(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article = (new ArticleBuilder())->withSlug('published-get-guard')->published()->build();
        $repo->save($article);
        $em->flush();
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/delete');

        self::assertResponseRedirects();
        self::assertStringContainsString('/admin/articles/'.$id.'/edit', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    /**
     * Défense en profondeur : même si un attaquant forge un POST sur un article
     * Draft, le contrôleur refuse (garde de statut) avant d'atteindre le handler.
     * L'invariant handler est couvert séparément dans DeleteArchivedArticleHandlerTest.
     */
    public function testDeleteDraftArticleIsRefused(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('draft-post-guard');
        $id = $article->id()->toRfc4122();

        $this->client->request('POST', '/admin/articles/'.$id.'/delete', ['_csrf_token' => 'any-value']);

        self::assertResponseRedirects();
        self::assertNotNull($this->tryReloadArticle($id), 'Un article Draft ne doit pas être supprimable.');
    }

    /**
     * Défense en profondeur : POST forgé sur un article Published → le
     * contrôleur refuse (garde de statut) avant d'atteindre le handler.
     */
    public function testDeletePublishedArticleIsRefused(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article = (new ArticleBuilder())->withSlug('published-post-guard')->published()->build();
        $repo->save($article);
        $em->flush();
        $id = $article->id()->toRfc4122();

        $this->client->request('POST', '/admin/articles/'.$id.'/delete', ['_csrf_token' => 'any-value']);

        self::assertResponseRedirects();
        self::assertNotNull($this->tryReloadArticle($id), 'Un article Published ne doit pas être supprimable.');
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
