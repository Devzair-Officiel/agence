<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Editorial\Domain\ArticleHeroImage;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\EditorialMedia\Application\Command\UploadEditorialMedia;
use App\EditorialMedia\Application\Command\UploadEditorialMediaHandler;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Domain\MediaAsset;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use App\Tests\EditorialMedia\Support\EditorialMediaDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Uid\Uuid;

/**
 * Couverture bout-à-bout de `AdminArticleImageDownloadController`.
 *
 * Tests :
 *   1. Article avec image → 200 + Content-Disposition attachment ;
 *   2. Content-Type correct (image/png) ;
 *   3. Nom de fichier propre : {slug}-image.{ext} ;
 *   4. Article sans image → 404 ;
 *   5. UUID inconnu → 404 ;
 *   6. POST → 405 ;
 *   7. Anonyme → redirection login ;
 *   8. Impossible de lire un fichier arbitraire via l'endpoint.
 */
final class AdminArticleImageDownloadControllerTest extends WebTestCase
{
    use EditorialDatabaseCleanup;
    use EditorialMediaDatabaseCleanup;

    private KernelBrowser $client;
    private string $storageDir;
    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        if (!\extension_loaded('gd')) {
            self::markTestSkipped('Extension GD requise pour générer les images de test.');
        }

        self::ensureKernelShutdown();
        $this->client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        AdminDatabaseCleanup::purge($em);
        $this->clearEditorialTables($em);
        $this->clearEditorialMediaTables($em);

        $this->storageDir = (string) self::getContainer()
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

    public function testAnonymousRedirectsToLogin(): void
    {
        $this->client->request('GET', '/admin/articles/'.Uuid::v7()->toRfc4122().'/image/download');

        self::assertResponseRedirects();
        self::assertStringContainsString('/admin/login', $this->client->getResponse()->headers->get('Location') ?? '');
    }

    public function testArticleWithImageReturns200AndAttachment(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        [$article, ] = $this->seedArticleWithImage('download-happy-path');
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/image/download');

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(StreamedResponse::class, $response);

        $disposition = (string) $response->headers->get('Content-Disposition');
        self::assertStringStartsWith('attachment;', $disposition);
    }

    public function testContentTypeMatchesUploadedFile(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        [$article, ] = $this->seedArticleWithImage('download-content-type');
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/image/download');

        self::assertResponseIsSuccessful();
        self::assertSame('image/png', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testFilenameFollowsSlugPattern(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $slug = 'download-filename-test';
        [$article, ] = $this->seedArticleWithImage($slug);
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/image/download');

        self::assertResponseIsSuccessful();
        $disposition = (string) $this->client->getResponse()->headers->get('Content-Disposition');
        self::assertStringContainsString($slug.'-image.png', $disposition);
    }

    public function testArticleWithoutImageReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article = (new ArticleBuilder())->withSlug('no-image-download')->build();
        $repo->save($article);
        $em->flush();
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/image/download');

        self::assertResponseStatusCodeSame(404);
    }

    public function testUnknownArticleReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/articles/'.Uuid::v7()->toRfc4122().'/image/download');

        self::assertResponseStatusCodeSame(404);
    }

    public function testPostReturnsMethodNotAllowed(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        [$article, ] = $this->seedArticleWithImage('download-post-method');
        $id = $article->id()->toRfc4122();

        $this->client->request('POST', '/admin/articles/'.$id.'/image/download');

        self::assertResponseStatusCodeSame(405);
    }

    public function testStorageKeyIsNotExposedInResponseHeaders(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        [$article, $asset] = $this->seedArticleWithImage('download-no-leak');
        $id = $article->id()->toRfc4122();

        $this->client->request('GET', '/admin/articles/'.$id.'/image/download');

        $response = $this->client->getResponse();
        $headerDump = json_encode(iterator_to_array($response->headers), \JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString(
            $asset->storageKey()->toString(),
            $headerDump,
            'La clé de stockage interne ne doit jamais fuir via un en-tête HTTP.',
        );
    }

    /**
     * @return array{0: \App\Editorial\Domain\Article, 1: MediaAsset}
     */
    private function seedArticleWithImage(string $slug): array
    {
        $asset = $this->uploadRealPng();

        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $article = (new ArticleBuilder())
            ->withSlug($slug)
            ->withNow(new \DateTimeImmutable('2026-09-01T10:00:00+00:00'))
            ->build();
        $article->changeHeroImage(
            ArticleHeroImage::create($asset->id(), 'Texte alternatif du test.'),
            new \DateTimeImmutable('2026-09-01T10:05:00+00:00'),
        );
        $repo->save($article);
        $em->flush();
        $em->clear();

        $reloaded = $repo->findById($article->id());
        \assert($reloaded !== null);

        return [$reloaded, $asset];
    }

    private function uploadRealPng(): MediaAsset
    {
        $path = tempnam(sys_get_temp_dir(), 'dl-src-');
        self::assertNotFalse($path);
        $this->tempFiles[] = $path;

        $image = imagecreatetruecolor(80, 60);
        self::assertNotFalse($image);
        $c = imagecolorallocate($image, 50, 100, 150);
        self::assertNotFalse($c);
        imagefilledrectangle($image, 0, 0, 79, 59, $c);
        imagepng($image, $path, 6);
        imagedestroy($image);

        $handler = self::getContainer()->get(UploadEditorialMediaHandler::class);
        $result = $handler(new UploadEditorialMedia(
            sourcePath: $path,
            originalFilename: 'download-test.png',
        ));

        $storage = self::getContainer()->get(MediaStorageInterface::class);
        self::assertTrue($storage->exists($result->asset->storageKey()));

        return $result->asset;
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
