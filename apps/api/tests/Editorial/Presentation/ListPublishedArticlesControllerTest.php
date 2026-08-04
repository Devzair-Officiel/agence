<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation;

use App\Editorial\Infrastructure\Persistence\DoctrineArticleRepository;
use App\Editorial\Presentation\Http\ListPublishedArticlesController;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test HTTP fonctionnel — vérifie le contrat JSON + les entêtes cache.
 * Chaque test boote un kernel neuf via createClient et s'appuie sur la
 * base `devzair_test`.
 */
#[CoversClass(ListPublishedArticlesController::class)]
final class ListPublishedArticlesControllerTest extends WebTestCase
{
    use EditorialDatabaseCleanup;

    public function testReturnsEmptyListWhenNoArticle(): void
    {
        $client = self::createClient();
        $this->resetDatabase();

        $client->request('GET', '/resources');

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame([], $data['items']);
        self::assertSame(0, $data['pagination']['total']);
        // Symfony trie les directives alphabétiquement dans HeaderBag —
        // c'est la représentation canonique retournée sur le fil.
        self::assertSame('max-age=60, public, s-maxage=300', $client->getResponse()->headers->get('Cache-Control'));
        self::assertNotEmpty($data['request_id']);
        self::assertSame($data['request_id'], $client->getResponse()->headers->get('X-Request-Id'));
    }

    public function testListsPublishedArticlesInJsonContract(): void
    {
        $client = self::createClient();
        $this->resetDatabase();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo = self::getContainer()->get(DoctrineArticleRepository::class);

        $repo->save((new ArticleBuilder())
            ->withSlug('article-le-plus-recent')
            ->withNow(new \DateTimeImmutable('2026-08-04T12:00:00+00:00'))
            ->published()
            ->build());
        $repo->save((new ArticleBuilder())
            ->withSlug('article-plus-ancien')
            ->withNow(new \DateTimeImmutable('2026-08-01T12:00:00+00:00'))
            ->published()
            ->build());
        $em->flush();

        $client->request('GET', '/resources');

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertCount(2, $data['items']);
        self::assertSame('article-le-plus-recent', $data['items'][0]['slug']);
        self::assertArrayHasKey('excerpt', $data['items'][0]);
        self::assertArrayHasKey('author', $data['items'][0]);
        self::assertArrayHasKey('expertise_ids', $data['items'][0]);
        self::assertArrayHasKey('published_at', $data['items'][0]);
        self::assertArrayNotHasKey('body_markdown', $data['items'][0]);
    }

    public function testInvalidPageReturns400Validation(): void
    {
        $client = self::createClient();

        $client->request('GET', '/resources?page=0');

        self::assertResponseStatusCodeSame(400);
        $data = $this->decode($client);
        self::assertSame('validation_error', $data['code']);
        self::assertNotEmpty($data['errors']);
    }

    public function testInvalidPerPageReturns400(): void
    {
        $client = self::createClient();

        $client->request('GET', '/resources?per_page=999');

        self::assertResponseStatusCodeSame(400);
        $data = $this->decode($client);
        self::assertSame('validation_error', $data['code']);
    }

    public function testNonDigitPageReturns400(): void
    {
        $client = self::createClient();

        $client->request('GET', '/resources?page=abc');

        self::assertResponseStatusCodeSame(400);
        self::assertSame('validation_error', $this->decode($client)['code']);
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(KernelBrowser $client): array
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }

    private function resetDatabase(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->clearEditorialTables($em);
    }
}
