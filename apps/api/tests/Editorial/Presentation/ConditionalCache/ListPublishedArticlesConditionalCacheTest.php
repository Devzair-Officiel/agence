<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation\ConditionalCache;

use App\Editorial\Infrastructure\Persistence\DoctrineArticleRepository;
use App\Editorial\Presentation\Http\ListPublishedArticlesController;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(ListPublishedArticlesController::class)]
final class ListPublishedArticlesConditionalCacheTest extends WebTestCase
{
    use EditorialDatabaseCleanup;

    public function testResponseAdvertisesWeakEtagAndNoLastModified(): void
    {
        $client = self::createClient();
        $this->seedTwoArticles();

        $client->request('GET', '/resources');

        $response = $client->getResponse();
        self::assertResponseStatusCodeSame(200);
        $etag = $response->headers->get('ETag');
        self::assertIsString($etag);
        // Format strict RFC 7232 : W/"<sha256 hex>", pas de double enveloppe.
        self::assertMatchesRegularExpression('/^W\/"[a-f0-9]{64}"$/', $etag);
        // La liste n'expose PAS de Last-Modified — sémantique HTTP réservée
        // à une ressource unique, pas à un tri paginé.
        self::assertNull($response->headers->get('Last-Modified'));
    }

    public function testReturns304OnMatchingIfNoneMatch(): void
    {
        $client = self::createClient();
        $this->seedTwoArticles();

        $client->request('GET', '/resources');
        $etag = $client->getResponse()->headers->get('ETag');
        self::assertIsString($etag);

        $client->request(
            'GET',
            '/resources',
            [],
            [],
            ['HTTP_IF_NONE_MATCH' => $etag],
        );

        $response = $client->getResponse();
        self::assertResponseStatusCodeSame(304);
        self::assertNotEmpty($response->headers->get('X-Request-Id'));
    }

    public function testDifferentEtagPerPage(): void
    {
        $client = self::createClient();
        $this->resetDatabase();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo = self::getContainer()->get(DoctrineArticleRepository::class);
        // 3 articles pour avoir 2 pages avec per_page=2
        $repo->save((new ArticleBuilder())
            ->withSlug('list-etag-a')
            ->withNow(new \DateTimeImmutable('2026-08-04T12:00:00+00:00'))
            ->published()
            ->build());
        $repo->save((new ArticleBuilder())
            ->withSlug('list-etag-b')
            ->withNow(new \DateTimeImmutable('2026-08-03T12:00:00+00:00'))
            ->published()
            ->build());
        $repo->save((new ArticleBuilder())
            ->withSlug('list-etag-c')
            ->withNow(new \DateTimeImmutable('2026-08-02T12:00:00+00:00'))
            ->published()
            ->build());
        $em->flush();

        $client->request('GET', '/resources?per_page=2&page=1');
        $etagPage1 = $client->getResponse()->headers->get('ETag');
        $client->request('GET', '/resources?per_page=2&page=2');
        $etagPage2 = $client->getResponse()->headers->get('ETag');

        self::assertNotSame($etagPage1, $etagPage2);
    }

    private function seedTwoArticles(): void
    {
        $this->resetDatabase();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo = self::getContainer()->get(DoctrineArticleRepository::class);
        $repo->save((new ArticleBuilder())
            ->withSlug('list-cache-a')
            ->withNow(new \DateTimeImmutable('2026-08-04T12:00:00+00:00'))
            ->published()
            ->build());
        $repo->save((new ArticleBuilder())
            ->withSlug('list-cache-b')
            ->withNow(new \DateTimeImmutable('2026-08-01T12:00:00+00:00'))
            ->published()
            ->build());
        $em->flush();
    }

    private function resetDatabase(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->clearEditorialTables($em);
    }
}
