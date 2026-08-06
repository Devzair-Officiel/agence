<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Bout-à-bout HTTP de l'édition d'un brouillon. Vérifie surtout le noyau
 * de sécurité : slug immuable, CSRF, refus si l'article n'est pas Draft.
 */
final class AdminArticleEditControllerTest extends WebTestCase
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

    public function testGetFormRendersReadOnlySlug(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('draft-edit-alpha');

        $crawler = $this->client->request('GET', '/admin/articles/'.$article->id()->toRfc4122().'/edit');

        self::assertResponseIsSuccessful();
        // Slug affiché en lecture seule (via <code>) mais pas exposé comme input :
        self::assertSame(0, $crawler->filterXPath('//input[@name="slug"]')->count());
        self::assertGreaterThan(0, $crawler->filterXPath('//code[normalize-space(text())="draft-edit-alpha"]')->count());
    }

    public function testSubmittingSlugFromRequestIsIgnored(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('draft-edit-locked');
        $id = $article->id()->toRfc4122();

        $token = $this->fetchEditToken($id);

        $this->client->request('POST', '/admin/articles/'.$id.'/edit', [
            '_csrf_token' => $token,
            'slug' => 'attempt-hijack-slug',
            'title' => $article->title(),
            'excerpt' => $article->excerpt(),
            'body_markdown' => $article->bodyMarkdown(),
            'seo_title' => $article->seo()->title(),
            'seo_description' => $article->seo()->description(),
            'author_name' => $article->author()->name(),
            'author_type' => $article->author()->type()->value,
            'expertises' => ['concevoir'],
        ]);

        self::assertResponseRedirects();
        // La base ne doit toujours contenir que le slug initial :
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        self::assertNotNull($repo->findBySlug(ArticleSlug::fromString('draft-edit-locked')));
        self::assertNull($repo->findBySlug(ArticleSlug::fromString('attempt-hijack-slug')));
    }

    public function testEditingPublishedArticleReturns409(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        // On crée d'abord l'article en Draft pour capturer un jeton CSRF valide
        // via un GET (le formulaire s'y trouve encore). Puis on publie et on
        // rejoue le POST — l'article n'est plus éditable, l'infra CSRF est
        // séparée de l'invariant métier, la réponse doit être 409.
        $article = (new ArticleBuilder())->withSlug('published-edit-lock')->build();
        $repo->save($article);
        $em->flush();
        $id = $article->id()->toRfc4122();

        $token = $this->fetchEditToken($id);

        // Publication après capture du jeton :
        $article->publish(new \DateTimeImmutable('2026-08-06T10:00:00+00:00'), new \DateTimeImmutable('2026-08-06T10:00:00+00:00'));
        $repo->save($article);
        $em->flush();

        $this->client->request('POST', '/admin/articles/'.$id.'/edit', [
            '_csrf_token' => $token,
            'title' => 'Nouveau titre — tentative',
            'excerpt' => $article->excerpt(),
            'body_markdown' => $article->bodyMarkdown(),
            'seo_title' => $article->seo()->title(),
            'seo_description' => $article->seo()->description(),
            'author_name' => $article->author()->name(),
            'author_type' => $article->author()->type()->value,
            'expertises' => ['concevoir'],
        ]);

        self::assertResponseStatusCodeSame(409);
    }

    public function testInvalidUuidReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/articles/not-a-uuid/edit');

        self::assertResponseStatusCodeSame(404);
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

    private function fetchEditToken(string $id): string
    {
        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/edit');
        $token = $crawler->filterXPath('//form[contains(@class, "article-form")]//input[@name="_csrf_token"]')->attr('value');
        \assert(\is_string($token) && $token !== '');

        return $token;
    }
}
