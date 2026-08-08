<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\EditorialMedia\Application\Policy\MediaUploadPolicy;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Infrastructure\Security\AdminMediaUploadRateLimiter;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\EditorialMedia\Support\EditorialMediaDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * Bout-à-bout HTTP `/admin/media/new` :
 *   - firewall admin actif ;
 *   - GET rend le formulaire avec un jeton CSRF ;
 *   - POST valide → 303 vers `/admin/media` + flash + asset persisté ;
 *   - CSRF absent/invalide → 403 sans invocation du handler ;
 *   - MIME refusé → 415 sans persistance ;
 *   - fichier trop lourd → 413 ;
 *   - image invalide → 422 ;
 *   - fichier manquant → 400 ;
 *   - rate limiter épuisé → 429 avec `Retry-After` ;
 *   - audit logger : événements uploaded / upload_failed / upload_rate_limited
 *     émis sur le canal `admin` sans PII.
 */
final class AdminMediaUploadControllerTest extends WebTestCase
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
        $this->client->request('GET', '/admin/media/new');

        self::assertResponseRedirects();
        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/admin/login', $location);
    }

    public function testGetRendersFormWithCsrfToken(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $crawler = $this->client->request('GET', '/admin/media/new');

        self::assertResponseIsSuccessful();
        $csrfInput = $crawler->filterXPath('//input[@name="_csrf_token"]');
        self::assertGreaterThan(0, $csrfInput->count());
        self::assertNotSame('', $csrfInput->attr('value'));
    }

    public function testPostWithoutCsrfTokenIsRejectedAsForbidden(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request(
            'POST',
            '/admin/media/new',
            [],
            ['file' => $this->uploadedImage(MediaType::Png, 200, 200)],
        );

        self::assertResponseStatusCodeSame(403);
        self::assertSame(
            0,
            $this->mediaCount(),
            'Aucun asset ne doit être persisté quand le CSRF est absent.',
        );

        $this->assertAuditContains('admin.media.upload_failed', 'csrf_invalid');
    }

    public function testPostValidPngRedirectsAndPersistsAsset(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $csrf = $this->openFormAndReadCsrf();

        $this->submitUpload(
            csrf: $csrf,
            file: $this->uploadedImage(MediaType::Png, 400, 300),
        );

        self::assertResponseStatusCodeSame(303);
        self::assertSame(
            '/admin/media',
            (string) $this->client->getResponse()->headers->get('Location'),
        );
        self::assertSame(1, $this->mediaCount());

        $this->assertAuditContainsUploadedInfo();
    }

    public function testPostUnsupportedMimeReturnsUnsupportedMediaType(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $csrf = $this->openFormAndReadCsrf();

        // GIF n'est pas dans la liste `MediaType` autorisée en 9A.
        $gif = $this->uploadedGif(50, 50);

        $this->submitUpload(csrf: $csrf, file: $gif);

        self::assertResponseStatusCodeSame(415);
        self::assertSame(0, $this->mediaCount());
        $this->assertAuditContains('admin.media.upload_failed', 'mime_unsupported');
    }

    public function testPostOversizedFileReturnsRequestEntityTooLarge(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $csrf = $this->openFormAndReadCsrf();

        // Fichier binaire brut qui dépasse la borne policy (MIME jpg déclaré
        // sur l'UploadedFile mais on ne s'en sert pas : le détecteur serveur
        // décide, mais la taille est vérifiée AVANT toute inspection MIME).
        $huge = tempnam(sys_get_temp_dir(), 'huge-');
        self::assertNotFalse($huge);
        $this->tempFiles[] = $huge;
        file_put_contents($huge, str_repeat('x', MediaUploadPolicy::MAX_SIZE_BYTES + 1));

        $file = new UploadedFile($huge, 'huge.jpg', 'image/jpeg', test: true);

        $this->submitUpload(csrf: $csrf, file: $file);

        self::assertResponseStatusCodeSame(413);
        self::assertSame(0, $this->mediaCount());
        $this->assertAuditContains('admin.media.upload_failed', 'file_too_large');
    }

    public function testPostInvalidImagePayloadReturnsUnprocessableEntity(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $csrf = $this->openFormAndReadCsrf();

        // Contenu texte + extension .png : le détecteur MIME serveur (finfo)
        // renvoie `text/plain`, la policy refuse en 415. On teste plutôt un
        // JPEG structurellement corrompu : le processeur GD échoue.
        $broken = tempnam(sys_get_temp_dir(), 'broken-');
        self::assertNotFalse($broken);
        $this->tempFiles[] = $broken;
        // On-disque : bytes JPEG SOI valides mais tronqués. finfo répondra
        // « image/jpeg », donc la policy passe ; puis GD refusera de décoder.
        file_put_contents($broken, "\xFF\xD8\xFF\xE0not-a-real-jpeg-body");

        $file = new UploadedFile($broken, 'broken.jpg', 'image/jpeg', test: true);

        $this->submitUpload(csrf: $csrf, file: $file);

        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, $this->mediaCount());
    }

    public function testPostMissingFileReturnsBadRequest(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $csrf = $this->openFormAndReadCsrf();

        $this->client->request(
            'POST',
            '/admin/media/new',
            ['_csrf_token' => $csrf],
        );

        self::assertResponseStatusCodeSame(400);
        self::assertSame(0, $this->mediaCount());
        $this->assertAuditContains('admin.media.upload_failed', 'file_missing');
    }

    public function testRateLimitedUploadReturns429WithRetryAfterHeader(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        // Le KernelBrowser rebootstrappe le kernel entre chaque requête par défaut,
        // ce qui effacerait le limiter injecté via container->set(). On désactive
        // le reboot pour préserver notre remplacement sur toute la durée du test.
        $this->client->disableReboot();
        $this->installTightRateLimiter();

        $csrf = $this->openFormAndReadCsrf();

        // Premier POST : consomme l'unique token disponible et réussit.
        $this->submitUpload(csrf: $csrf, file: $this->uploadedImage(MediaType::Jpeg, 100, 100));
        self::assertResponseStatusCodeSame(303);
        self::assertSame(1, $this->mediaCount());

        // Second POST : bucket vide → 429 + Retry-After.
        $csrf2 = $this->openFormAndReadCsrf();
        $this->submitUpload(csrf: $csrf2, file: $this->uploadedImage(MediaType::Jpeg, 100, 100));

        self::assertResponseStatusCodeSame(429);
        self::assertNotNull(
            $this->client->getResponse()->headers->get('Retry-After'),
            'La réponse 429 doit exposer un en-tête Retry-After.',
        );
        self::assertSame(
            1,
            $this->mediaCount(),
            'Le second POST doit être refusé AVANT toute persistance.',
        );
        $this->assertAuditContains('admin.media.upload_rate_limited', 'retry_after_seconds');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function submitUpload(string $csrf, UploadedFile $file): void
    {
        $this->client->request(
            'POST',
            '/admin/media/new',
            ['_csrf_token' => $csrf],
            ['file' => $file],
        );
    }

    private function openFormAndReadCsrf(): string
    {
        $crawler = $this->client->request('GET', '/admin/media/new');
        self::assertResponseIsSuccessful();
        $token = $crawler->filterXPath('//input[@name="_csrf_token"]')->attr('value');
        self::assertIsString($token);
        self::assertNotSame('', $token);

        return $token;
    }

    private function uploadedImage(MediaType $type, int $width, int $height): UploadedFile
    {
        $path = $this->makeImageFile($type, $width, $height);
        $ext = $type->extension();

        return new UploadedFile(
            $path,
            'photo.' . $ext,
            $type->mime(),
            test: true,
        );
    }

    private function uploadedGif(int $width, int $height): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        self::assertNotFalse($image);
        $color = imagecolorallocate($image, 12, 34, 56);
        self::assertNotFalse($color);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $color);

        $tmp = tempnam(sys_get_temp_dir(), 'upload-gif-');
        self::assertNotFalse($tmp);
        $this->tempFiles[] = $tmp;
        imagegif($image, $tmp);
        imagedestroy($image);

        return new UploadedFile($tmp, 'anim.gif', 'image/gif', test: true);
    }

    private function makeImageFile(MediaType $type, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        self::assertNotFalse($image);
        $color = imagecolorallocate($image, 200, 100, 50);
        self::assertNotFalse($color);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $color);

        $tmp = tempnam(sys_get_temp_dir(), 'upload-src-');
        self::assertNotFalse($tmp);
        $this->tempFiles[] = $tmp;

        switch ($type) {
            case MediaType::Jpeg:
                imagejpeg($image, $tmp, 85);
                break;
            case MediaType::Png:
                imagepng($image, $tmp, 6);
                break;
            case MediaType::WebP:
                if (!\function_exists('imagewebp')) {
                    self::markTestSkipped('Support WebP indisponible dans GD.');
                }
                imagewebp($image, $tmp, 82);
                break;
        }
        imagedestroy($image);

        return $tmp;
    }

    private function mediaCount(): int
    {
        return self::getContainer()
            ->get(MediaAssetRepositoryInterface::class)
            ->count();
    }

    private function installTightRateLimiter(): void
    {
        $factory = new RateLimiterFactory(
            [
                'policy' => 'token_bucket',
                'id' => 'admin_media_upload_test',
                'limit' => 1,
                'rate' => ['interval' => '1 hour', 'amount' => 1],
            ],
            new InMemoryStorage(),
        );
        self::getContainer()->set(
            AdminMediaUploadRateLimiter::class,
            new AdminMediaUploadRateLimiter($factory),
        );
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

    private function assertAuditContains(string $message, string $expectedKey): void
    {
        $handler = $this->adminTestHandler();
        $records = array_filter(
            $handler->getRecords(),
            static fn (\Monolog\LogRecord $r): bool => $r->channel === 'admin' && $r->message === $message,
        );
        self::assertNotEmpty(
            $records,
            \sprintf('Message « %s » attendu sur le canal admin.', $message),
        );
        $flat = json_encode(array_map(
            static fn (\Monolog\LogRecord $r): array => $r->context,
            array_values($records),
        ), \JSON_THROW_ON_ERROR);
        self::assertStringContainsString($expectedKey, $flat);
        self::assertStringNotContainsString('admin@devzair.local', $flat, 'Aucun email admin ne doit apparaître.');
    }

    private function assertAuditContainsUploadedInfo(): void
    {
        $handler = $this->adminTestHandler();
        self::assertTrue(
            $handler->hasInfoThatContains('admin.media.uploaded'),
            'Un événement admin.media.uploaded doit être émis en INFO.',
        );
        $records = array_values(array_filter(
            $handler->getRecords(),
            static fn (\Monolog\LogRecord $r): bool => $r->channel === 'admin' && $r->message === 'admin.media.uploaded',
        ));
        self::assertNotEmpty($records);
        $context = $records[0]->context;
        self::assertArrayHasKey('admin_id', $context);
        self::assertArrayHasKey('asset_id', $context);
        self::assertArrayHasKey('mime', $context);
        self::assertArrayHasKey('sha256_prefix', $context);
        self::assertSame(12, \strlen($context['sha256_prefix']));

        $flat = json_encode($context, \JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('admin@devzair.local', $flat);
    }
}
