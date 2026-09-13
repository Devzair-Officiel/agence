<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation;

use App\Editorial\Domain\ArticleHeroImage;
use App\Editorial\Infrastructure\Persistence\DoctrineArticleRepository;
use App\Editorial\Presentation\Http\GetPublicArticleMediaVariantController;
use App\EditorialMedia\Application\Command\UploadEditorialMedia;
use App\EditorialMedia\Application\Command\UploadEditorialMediaHandler;
use App\EditorialMedia\Domain\MediaAsset;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Uid\Uuid;

/**
 * Bout-à-bout HTTP `GET /api/media/{id}/{variant}` (Phase 9C).
 *
 * Contrats vérifiés :
 *   - variant non autorisé (`thumbnail`) filtré par contrainte de route → 404 ;
 *   - UUID malformé filtré par la contrainte de route → 404 ;
 *   - média inexistant → 404 ;
 *   - média orphelin (pas d'article publié) → 404 ;
 *   - média référencé par un Draft uniquement → 404 ;
 *   - succès : 200 + Content-Type image/webp + ETag + Cache-Control public ;
 *   - `If-None-Match` correspondant → 304 sans corps ;
 *   - archivage de l'article sole → 404 (révocation d'accès).
 */
#[CoversClass(GetPublicArticleMediaVariantController::class)]
final class GetPublicArticleMediaVariantControllerTest extends WebTestCase
{
    use EditorialDatabaseCleanup;

    private KernelBrowser $client;
    private string $storageDir;
    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        if (!\extension_loaded('gd')) {
            self::markTestSkipped('Extension GD requise pour générer les images du test.');
        }

        self::ensureKernelShutdown();
        $this->client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->clearEditorialTables($em);

        $this->storageDir = self::getContainer()
            ->getParameter('app.editorial_media.storage_dir');
        $this->purgeStorageDir();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        $this->tempFiles = [];
        $this->purgeStorageDir();
    }

    public function testInvalidVariantNameIsRejectedByRouteConstraint(): void
    {
        $uuid = Uuid::v7()->toRfc4122();
        $this->client->request('GET', "/media/{$uuid}/thumbnail");

        self::assertResponseStatusCodeSame(404);
    }

    public function testMalformedUuidIsRejectedByRouteRegex(): void
    {
        $this->client->request('GET', '/media/not-a-uuid/card');

        self::assertResponseStatusCodeSame(404);
    }

    public function testUnknownMediaReturns404(): void
    {
        $uuid = Uuid::v7()->toRfc4122();
        $this->client->request('GET', "/media/{$uuid}/card");

        self::assertResponseStatusCodeSame(404);
        self::assertStringContainsString(
            'no-store',
            (string) $this->client->getResponse()->headers->get('Cache-Control'),
        );
    }

    public function testOrphanMediaVariantReturns404(): void
    {
        $asset = $this->uploadRealJpeg();

        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122() . '/card');

        self::assertResponseStatusCodeSame(404);
    }

    public function testMediaReferencedByDraftOnlyVariantReturns404(): void
    {
        $asset = $this->uploadRealJpeg();
        $this->attachAsHero($asset, publish: false);

        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122() . '/card');

        self::assertResponseStatusCodeSame(404);
    }

    public function testCardVariantIsStreamedWithCorrectHeaders(): void
    {
        $asset = $this->uploadRealJpeg();
        $this->attachAsHero($asset, publish: true);

        self::assertNotNull($asset->cardWidth(), 'Le pipeline doit générer les variants WebP.');

        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122() . '/card');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(StreamedResponse::class, $response);

        $headers = $response->headers;
        self::assertSame('image/webp', $headers->get('Content-Type'));

        $cacheControl = (string) $headers->get('Cache-Control');
        self::assertStringContainsString('public', $cacheControl);
        self::assertStringContainsString('max-age=3600', $cacheControl);
        self::assertStringContainsString('must-revalidate', $cacheControl);
        self::assertStringNotContainsString('immutable', $cacheControl);

        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('index, follow', $headers->get('X-Robots-Tag'));

        // ETag fort (sha256 du variant)
        $etag = (string) $headers->get('ETag');
        self::assertMatchesRegularExpression('/^"[a-f0-9]+"$/', $etag);
        self::assertStringStartsNotWith('W/', $etag);

        // Corps décodable WebP
        $body = $this->client->getInternalResponse()->getContent();
        self::assertNotEmpty($body);
        $img = @imagecreatefromstring($body);
        self::assertInstanceOf(\GdImage::class, $img, 'Le corps doit être un WebP décodable.');
        imagedestroy($img);
    }

    public function testHeroVariantIsStreamedSuccessfully(): void
    {
        $asset = $this->uploadRealJpeg();
        $this->attachAsHero($asset, publish: true);

        self::assertNotNull($asset->heroWidth(), 'Le pipeline doit générer le variant hero.');

        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122() . '/hero');

        self::assertResponseIsSuccessful();
        self::assertSame('image/webp', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testIfNoneMatchReturns304WithoutBodyForVariant(): void
    {
        $asset = $this->uploadRealJpeg();
        $this->attachAsHero($asset, publish: true);

        $url = '/media/' . $asset->id()->toRfc4122() . '/card';

        $this->client->request('GET', $url);
        self::assertResponseIsSuccessful();
        $etag = (string) $this->client->getResponse()->headers->get('ETag');
        self::assertNotSame('', $etag);

        $this->client->request('GET', $url, server: ['HTTP_IF_NONE_MATCH' => $etag]);

        self::assertResponseStatusCodeSame(304);
        self::assertSame('', $this->client->getInternalResponse()->getContent());
    }

    public function testArchivingSoleArticleFlipsVariantTo404(): void
    {
        $asset = $this->uploadRealJpeg();
        $article = $this->attachAsHero($asset, publish: true);

        $url = '/media/' . $asset->id()->toRfc4122() . '/card';
        $this->client->request('GET', $url);
        self::assertResponseIsSuccessful();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article->archive(new \DateTimeImmutable('2026-09-14T08:00:00+00:00'));
        $em->flush();
        $em->clear();

        $this->client->request('GET', $url);
        self::assertResponseStatusCodeSame(404);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function uploadRealJpeg(): MediaAsset
    {
        $path = tempnam(sys_get_temp_dir(), 'variant-ctrl-src-');
        self::assertNotFalse($path);
        $this->tempFiles[] = $path;

        $img = imagecreatetruecolor(320, 240);
        self::assertInstanceOf(\GdImage::class, $img);
        $c = imagecolorallocate($img, 50, 100, 150);
        self::assertNotFalse($c);
        imagefilledrectangle($img, 0, 0, 319, 239, $c);
        imagejpeg($img, $path, 85);
        imagedestroy($img);

        $handler = self::getContainer()->get(UploadEditorialMediaHandler::class);
        $result  = $handler(new UploadEditorialMedia(
            sourcePath: $path,
            originalFilename: 'variant-ctrl-test.jpg',
        ));

        return $result->asset;
    }

    private function attachAsHero(MediaAsset $asset, bool $publish, ?string $slug = null): \App\Editorial\Domain\Article
    {
        $em          = self::getContainer()->get(EntityManagerInterface::class);
        $articleRepo = self::getContainer()->get(DoctrineArticleRepository::class);

        $builder = (new ArticleBuilder())
            ->withSlug($slug ?? ($publish ? 'article-variant-ctrl' : 'draft-variant-ctrl'))
            ->withHeroImage(ArticleHeroImage::create($asset->id(), 'Alt variant test.'));

        if ($publish) {
            $builder = $builder->published();
        }

        $article = $builder->build();
        $articleRepo->save($article);
        $em->flush();

        return $article;
    }

    private function purgeStorageDir(): void
    {
        if (!is_dir($this->storageDir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->storageDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $entry) {
            $entry->isDir() ? @rmdir($entry->getPathname()) : @unlink($entry->getPathname());
        }
    }
}
