<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\EditorialMedia\Application\Command\UploadEditorialMedia;
use App\EditorialMedia\Application\Command\UploadEditorialMediaHandler;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use App\EditorialMedia\Domain\MediaType;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\EditorialMedia\Support\EditorialMediaDatabaseCleanup;
use App\Tests\EditorialMedia\Support\MediaAssetBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Uid\Uuid;

/**
 * Bout-à-bout HTTP `/admin/media/{id}/preview` :
 *   - firewall admin actif ;
 *   - GET uniquement (POST → 405) ;
 *   - UUID malformé → 404 ;
 *   - asset absent → 404 (aucune divulgation d'existence) ;
 *   - fichier absent sur disque → 404 ;
 *   - asset présent → 200 + binaire streamé + `Content-Type` fixé côté serveur ;
 *   - événement `admin.media.previewed` émis sur le canal `admin`.
 */
final class AdminMediaPreviewControllerTest extends WebTestCase
{
    use EditorialMediaDatabaseCleanup;

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
        AdminDatabaseCleanup::purge($em);
        $this->clearEditorialMediaTables($em);

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

    public function testUnauthenticatedIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin/media/' . Uuid::v7()->toRfc4122() . '/preview');

        self::assertResponseRedirects();
        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/admin/login', $location);
    }

    public function testMalformedUuidIsRejectedByRouteRegex(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        // La contrainte `[0-9a-fA-F-]{36}` de la route filtre en amont
        // → Symfony renvoie 404 sans même invoquer le contrôleur.
        $this->client->request('GET', '/admin/media/not-a-uuid/preview');

        self::assertResponseStatusCodeSame(404);
    }

    public function testUnknownAssetReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        // UUID formé correctement mais aucun asset en base.
        $this->client->request(
            'GET',
            '/admin/media/' . Uuid::v7()->toRfc4122() . '/preview',
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testAssetWithoutFileOnDiskReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        // Entité en base mais AUCUN fichier associé sur disque : le storage
        // lève MediaStorageException, que le contrôleur traduit en 404 sans
        // divulguer l'incident.
        $asset = (new MediaAssetBuilder())->build();
        $repo = self::getContainer()->get(MediaAssetRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo->save($asset);
        $em->flush();
        $em->clear();

        $this->client->request(
            'GET',
            '/admin/media/' . $asset->id()->toRfc4122() . '/preview',
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testValidAssetStreamsBinaryWithServerDefinedContentType(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $asset = $this->uploadRealPng();

        $this->client->request(
            'GET',
            '/admin/media/' . $asset->id()->toRfc4122() . '/preview',
        );

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertSame('image/png', $response->headers->get('Content-Type'));
        self::assertSame((string) $asset->sizeBytes(), $response->headers->get('Content-Length'));
        self::assertSame('inline', $response->headers->get('Content-Disposition'));

        // Le corps streamé doit être un PNG valide (magic bytes 0x89PNG).
        // HttpKernelBrowser::filterResponse a déjà consommé sendContent() et
        // capturé le body sur la DomResponse interne — on lit là.
        $body = $this->client->getInternalResponse()->getContent();
        self::assertStringStartsWith("\x89PNG", $body);
        self::assertSame($asset->sizeBytes(), \strlen($body));
    }

    public function testPreviewEmitsAdminInfoAuditEvent(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $asset = $this->uploadRealPng();

        $this->client->request(
            'GET',
            '/admin/media/' . $asset->id()->toRfc4122() . '/preview',
        );

        self::assertResponseIsSuccessful();

        $handler = $this->adminTestHandler();
        self::assertTrue(
            $handler->hasInfoThatContains('admin.media.previewed'),
            'Un événement admin.media.previewed doit être émis en INFO.',
        );

        $records = array_values(array_filter(
            $handler->getRecords(),
            static fn (\Monolog\LogRecord $r): bool => $r->channel === 'admin' && $r->message === 'admin.media.previewed',
        ));
        self::assertNotEmpty($records);
        $context = $records[0]->context;
        self::assertSame($asset->id()->toRfc4122(), $context['asset_id']);
        self::assertSame('image/png', $context['mime']);
        self::assertArrayHasKey('admin_id', $context);

        $flat = json_encode($context, \JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('admin@devzair.local', $flat);
    }

    public function testSecurityHeadersAreAppliedToPreview(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $asset = $this->uploadRealPng();

        $this->client->request(
            'GET',
            '/admin/media/' . $asset->id()->toRfc4122() . '/preview',
        );

        $headers = $this->client->getResponse()->headers;
        self::assertStringContainsString('no-store', (string) $headers->get('Cache-Control'));
        self::assertStringContainsString('private', (string) $headers->get('Cache-Control'));
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertSame('noindex, nofollow', $headers->get('X-Robots-Tag'));
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
    }

    public function testPostReturnsMethodNotAllowed(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $asset = $this->uploadRealPng();

        $this->client->request(
            'POST',
            '/admin/media/' . $asset->id()->toRfc4122() . '/preview',
        );

        self::assertResponseStatusCodeSame(405);
    }

    public function testStorageKeyIsNotExposedInResponse(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $asset = $this->uploadRealPng();

        $this->client->request(
            'GET',
            '/admin/media/' . $asset->id()->toRfc4122() . '/preview',
        );

        $response = $this->client->getResponse();
        $headerDump = json_encode(iterator_to_array($response->headers), \JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString(
            $asset->storageKey()->toString(),
            $headerDump,
            'La clé de stockage interne ne doit pas fuir via un header.',
        );
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function uploadRealPng(): MediaAsset
    {
        $path = tempnam(sys_get_temp_dir(), 'preview-src-');
        self::assertNotFalse($path);
        $this->tempFiles[] = $path;

        $image = imagecreatetruecolor(120, 80);
        self::assertNotFalse($image);
        $c = imagecolorallocate($image, 10, 20, 30);
        self::assertNotFalse($c);
        imagefilledrectangle($image, 0, 0, 119, 79, $c);
        imagepng($image, $path, 6);
        imagedestroy($image);

        $handler = self::getContainer()->get(UploadEditorialMediaHandler::class);
        $result = $handler(new UploadEditorialMedia(
            sourcePath: $path,
            originalFilename: 'preview.png',
        ));

        // On vérifie que le fichier existe bien dans le storage (le handler l'a
        // déplacé, `$path` n'existe plus).
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

    private function adminTestHandler(): TestHandler
    {
        /** @var iterable<HandlerInterface> $handlers */
        $handlers = self::getContainer()->get('monolog.logger.admin')->getHandlers();
        foreach ($handlers as $handler) {
            if ($handler instanceof TestHandler) {
                return $handler;
            }
        }
        self::fail('Aucun TestHandler branché sur le canal "admin".');
    }
}
