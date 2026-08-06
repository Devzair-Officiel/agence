<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\ArticleStatus;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie publish / archive / restore : chaque endpoint est POST-only,
 * exige un jeton CSRF valide, refuse GET et applique bien la transition.
 */
final class AdminArticleTransitionControllerTest extends WebTestCase
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

    public function testPublishRequiresPostAndValidCsrf(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('draft-publish-target');
        $id = $article->id()->toRfc4122();

        // GET est explicitement absent des méthodes autorisées : 405.
        $this->client->request('GET', '/admin/articles/'.$id.'/publish');
        self::assertResponseStatusCodeSame(405);

        // POST sans jeton : 302 → retour édition (flash), aucune transition.
        $this->client->request('POST', '/admin/articles/'.$id.'/publish');
        self::assertResponseRedirects();
        self::assertSame(
            ArticleStatus::Draft,
            $this->reloadArticle($id)->status(),
            'Un POST sans CSRF ne doit jamais publier.',
        );
    }

    public function testPublishHappyPathTransitionsDraftToPublished(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('draft-publish-happy');
        $id = $article->id()->toRfc4122();

        $token = $this->tokenFromEditForm($id, 'publish');
        $this->client->request('POST', '/admin/articles/'.$id.'/publish', ['_csrf_token' => $token]);

        self::assertResponseRedirects('/admin/articles');
        self::assertSame(ArticleStatus::Published, $this->reloadArticle($id)->status());
    }

    public function testArchiveFromPublishedTransitionsAndRedirectsToList(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article = (new ArticleBuilder())->withSlug('published-archive-target')->published()->build();
        $repo->save($article);
        $em->flush();
        $id = $article->id()->toRfc4122();

        $token = $this->tokenFromEditForm($id, 'archive');
        $this->client->request('POST', '/admin/articles/'.$id.'/archive', ['_csrf_token' => $token]);

        self::assertResponseRedirects('/admin/articles');
        self::assertSame(ArticleStatus::Archived, $this->reloadArticle($id)->status());
    }

    public function testRestoreFromArchivedTransitionsToDraft(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article = (new ArticleBuilder())->withSlug('archived-restore-target')->build();
        $article->archive(new \DateTimeImmutable('2026-08-06T10:00:00+00:00'));
        $repo->save($article);
        $em->flush();
        $id = $article->id()->toRfc4122();

        $token = $this->tokenFromEditForm($id, 'restore');
        $this->client->request('POST', '/admin/articles/'.$id.'/restore', ['_csrf_token' => $token]);

        self::assertResponseRedirects();
        self::assertSame(ArticleStatus::Draft, $this->reloadArticle($id)->status());
    }

    public function testRestoreFromPublishedIsRefused(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        // On construit un état Archived pour pouvoir capturer un jeton
        // « restore » légitime, puis on repasse Draft → Published via le
        // domaine avant de rejouer la requête. Le contrôleur doit refuser
        // la transition Published → Draft (invariant métier), indépendamment
        // de la validité structurelle du jeton CSRF.
        $article = (new ArticleBuilder())->withSlug('published-restore-refuse')->build();
        $article->archive(new \DateTimeImmutable('2026-08-06T10:00:00+00:00'));
        $repo->save($article);
        $em->flush();
        $id = $article->id()->toRfc4122();

        $token = $this->tokenFromEditForm($id, 'restore');

        // Rétablir en Draft puis publier — via domaine, sans HTTP :
        $article->restore(new \DateTimeImmutable('2026-08-06T11:00:00+00:00'));
        $article->publish(new \DateTimeImmutable('2026-08-06T11:05:00+00:00'), new \DateTimeImmutable('2026-08-06T11:05:00+00:00'));
        $repo->save($article);
        $em->flush();

        $this->client->request('POST', '/admin/articles/'.$id.'/restore', ['_csrf_token' => $token]);

        self::assertResponseRedirects();
        self::assertSame(ArticleStatus::Published, $this->reloadArticle($id)->status());
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

    /**
     * Récupère un jeton CSRF exposé par le formulaire de l'action visée sur
     * la page d'édition. Le manager CSRF Symfony ne fonctionne pas hors
     * d'une requête (il lit dans la session en cours), donc on passe par le
     * rendu réel : chaque formulaire d'action porte son propre `_csrf_token`.
     *
     * `$actionSuffix` = `publish`, `archive` ou `restore`.
     */
    private function tokenFromEditForm(string $id, string $actionSuffix): string
    {
        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/edit');
        $xpath = \sprintf(
            '//form[contains(@action, "/admin/articles/%s/%s")]//input[@name="_csrf_token"]',
            $id,
            $actionSuffix,
        );
        $node = $crawler->filterXPath($xpath);
        \assert($node->count() > 0, \sprintf('Formulaire %s introuvable pour %s.', $actionSuffix, $id));
        $token = $node->attr('value');
        \assert(\is_string($token) && $token !== '');

        return $token;
    }

    private function reloadArticle(string $id): Article
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $article = $repo->findById(\Symfony\Component\Uid\Uuid::fromString($id));
        \assert($article instanceof Article, \sprintf('Article %s introuvable.', $id));

        return $article;
    }
}
