<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation\ConditionalCache;

use App\Editorial\Infrastructure\Persistence\DoctrineArticleRepository;
use App\Editorial\Presentation\Http\GetPublishedArticleController;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie le pipeline ETag/Last-Modified/304 du détail article.
 */
#[CoversClass(GetPublishedArticleController::class)]
final class GetPublishedArticleConditionalCacheTest extends WebTestCase
{
    use EditorialDatabaseCleanup;

    public function testResponseAdvertisesWeakEtagAndLastModified(): void
    {
        $client = self::createClient();
        $this->seedPublishedArticle('detail-cache-headers');

        $client->request('GET', '/resources/detail-cache-headers');

        $response = $client->getResponse();
        self::assertResponseStatusCodeSame(200);
        $etag = $response->headers->get('ETag');
        self::assertIsString($etag);
        // Format strict RFC 7232 : W/"<sha256 hex>", pas de double enveloppe.
        self::assertMatchesRegularExpression('/^W\/"[a-f0-9]{64}"$/', $etag);
        self::assertNotNull($response->headers->get('Last-Modified'));
    }

    public function testReturns304OnMatchingIfNoneMatch(): void
    {
        $client = self::createClient();
        $this->seedPublishedArticle('detail-cache-304');

        $client->request('GET', '/resources/detail-cache-304');
        $response = $client->getResponse();
        $etag = $response->headers->get('ETag');
        $requestId1 = $response->headers->get('X-Request-Id');
        self::assertIsString($etag);
        self::assertNotEmpty($requestId1);

        $client->request(
            'GET',
            '/resources/detail-cache-304',
            [],
            [],
            ['HTTP_IF_NONE_MATCH' => $etag],
        );

        $response = $client->getResponse();
        self::assertResponseStatusCodeSame(304);
        self::assertSame('', $response->getContent() === false ? '' : $response->getContent());
        self::assertNotEmpty($response->headers->get('X-Request-Id'));
    }

    public function testReturns304OnIfModifiedSinceEqualOrLater(): void
    {
        $client = self::createClient();
        $this->seedPublishedArticle('detail-ims');

        $client->request('GET', '/resources/detail-ims');
        $lastModified = $client->getResponse()->headers->get('Last-Modified');
        self::assertIsString($lastModified);

        $client->request(
            'GET',
            '/resources/detail-ims',
            [],
            [],
            ['HTTP_IF_MODIFIED_SINCE' => $lastModified],
        );

        self::assertResponseStatusCodeSame(304);
        self::assertNotEmpty($client->getResponse()->headers->get('X-Request-Id'));
    }

    public function testDifferentEtagPerArticleContent(): void
    {
        $client = self::createClient();
        $this->resetDatabase();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo = self::getContainer()->get(DoctrineArticleRepository::class);
        $repo->save((new ArticleBuilder())->withSlug('detail-etag-a')->published()->build());
        $repo->save((new ArticleBuilder())->withSlug('detail-etag-b')->published()->build());
        $em->flush();

        $client->request('GET', '/resources/detail-etag-a');
        $etagA = $client->getResponse()->headers->get('ETag');
        $client->request('GET', '/resources/detail-etag-b');
        $etagB = $client->getResponse()->headers->get('ETag');

        self::assertNotSame($etagA, $etagB);
    }

    private function seedPublishedArticle(string $slug): void
    {
        $this->resetDatabase();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo = self::getContainer()->get(DoctrineArticleRepository::class);
        $repo->save((new ArticleBuilder())->withSlug($slug)->published()->build());
        $em->flush();
    }

    private function resetDatabase(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->clearEditorialTables($em);
    }
}
