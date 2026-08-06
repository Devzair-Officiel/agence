<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

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
 * Bout-à-bout HTTP de la création d'article.
 */
final class AdminArticleCreateControllerTest extends WebTestCase
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

    public function testGetRendersFormWithCsrfToken(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $crawler = $this->client->request('GET', '/admin/articles/new');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filterXPath('//input[@name="_csrf_token"]')->count());
        self::assertGreaterThan(0, $crawler->filterXPath('//input[@name="slug"]')->count());
    }

    public function testValidSubmissionCreatesDraftAndRedirectsToEdit(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->submitValidForm();

        self::assertResponseRedirects();
        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertMatchesRegularExpression('#/admin/articles/[0-9a-f-]{36}/edit#', $location);

        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $article = $repo->findBySlug(ArticleSlug::fromString('test-http-create'));
        self::assertNotNull($article);
    }

    public function testInvalidCsrfTokenReturns403WithoutPersisting(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->client->request('GET', '/admin/articles/new');

        $this->client->request('POST', '/admin/articles/new', $this->validPayload() + ['_csrf_token' => 'wrong']);

        self::assertResponseStatusCodeSame(403);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        self::assertNull($repo->findBySlug(ArticleSlug::fromString('test-http-create')));
    }

    public function testDuplicateSlugReturns409(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo->save((new ArticleBuilder())->withSlug('test-http-create')->build());
        $em->flush();

        $this->submitValidForm();

        self::assertResponseStatusCodeSame(409);
    }

    public function testInvalidMarkdownReturns422(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $payload = $this->validPayload();
        $payload['body_markdown'] = '<script>alert(1)</script>';
        $this->submitForm($payload);

        self::assertResponseStatusCodeSame(422);
    }

    private function submitValidForm(): void
    {
        $this->submitForm($this->validPayload());
    }

    private function submitForm(array $payload): void
    {
        $crawler = $this->client->request('GET', '/admin/articles/new');
        $token = $crawler->filterXPath('//input[@name="_csrf_token"]')->attr('value');
        \assert(\is_string($token) && $token !== '');
        $this->client->request('POST', '/admin/articles/new', $payload + ['_csrf_token' => $token]);
    }

    private function validPayload(): array
    {
        return [
            'slug' => 'test-http-create',
            'title' => 'Article créé via test HTTP',
            'excerpt' => 'Un chapô suffisamment long pour respecter la borne minimale imposée par le domaine.',
            'body_markdown' => "## Introduction\n\nCorps Markdown minimal — mais assez pour être crédible.",
            'seo_title' => 'Titre SEO de test HTTP création article',
            'seo_description' => 'Description SEO — assez longue pour dépasser la borne minimale imposée à un article.',
            'author_name' => 'Devzair',
            'author_type' => 'organization',
            'expertises' => ['concevoir'],
        ];
    }
}
