<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation;

use App\Editorial\Domain\ArticleHeroImage;
use App\Editorial\Infrastructure\Persistence\DoctrineArticleRepository;
use App\Editorial\Presentation\Http\GetPublicArticleMediaController;
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
 * Bout-à-bout HTTP `GET /api/media/{id}` (Phase 9B — route publique de
 * diffusion des médias éditoriaux).
 *
 * Le contrat vérifié ici :
 *   - UUID malformé filtré par la contrainte de route → 404 sans invoquer
 *     le contrôleur ;
 *   - média inexistant → 404 (aucune divulgation) ;
 *   - média existant mais non référencé par un article publié → 404 ;
 *   - média référencé par un Draft → 404 (règle « publié uniquement ») ;
 *   - succès : 200 + Content-Type serveur + ETag fort + Last-Modified
 *     + Cache-Control public 1h + X-Content-Type-Options + X-Robots-Tag ;
 *   - `If-None-Match` correspondant → 304 sans corps ;
 *   - `If-Modified-Since` récent → 304 ;
 *   - un article archivé après publication invalide la diffusion (retour 404).
 *
 * On seed les médias via `UploadEditorialMediaHandler` pour garantir que le
 * fichier physique (WebP normalisé) est réellement présent dans le storage
 * de test — c'est ce que la route va streamer.
 */
#[CoversClass(GetPublicArticleMediaController::class)]
final class GetPublicArticleMediaControllerTest extends WebTestCase
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

    public function testMalformedUuidIsRejectedByRouteRegex(): void
    {
        $this->client->request('GET', '/media/not-a-uuid');

        // La contrainte `[0-9a-fA-F-]{36}` de la route filtre en amont :
        // Symfony renvoie 404 sans invoquer le contrôleur.
        self::assertResponseStatusCodeSame(404);
    }

    public function testUnknownMediaReturns404(): void
    {
        $this->client->request('GET', '/media/' . Uuid::v7()->toRfc4122());

        self::assertResponseStatusCodeSame(404);
        // Le contrôleur pose `no-store` ; un listener sécurité peut y ajouter
        // `private` — on vérifie la substance sans figer l'ordre exact.
        self::assertStringContainsString(
            'no-store',
            (string) $this->client->getResponse()->headers->get('Cache-Control'),
        );
    }

    public function testOrphanMediaReturns404(): void
    {
        // Le média existe physiquement + en base, mais AUCUN article publié
        // ne le référence — la porte doit dire non et le contrôleur 404.
        $this->uploadRealPng();

        $this->client->request('GET', '/media/' . $this->lastAssetId()->toRfc4122());

        self::assertResponseStatusCodeSame(404);
    }

    public function testMediaReferencedByDraftOnlyReturns404(): void
    {
        $asset = $this->uploadRealPng();

        $this->attachAsHero($asset, publish: false);

        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122());

        self::assertResponseStatusCodeSame(404);
    }

    public function testMediaReferencedByPublishedArticleIsStreamedWithSecurityHeaders(): void
    {
        $asset = $this->uploadRealPng();
        $this->attachAsHero($asset, publish: true);

        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122());

        self::assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        self::assertInstanceOf(StreamedResponse::class, $response);

        $headers = $response->headers;
        // Content-Type posé côté serveur depuis l'agrégat, jamais deviné côté
        // client. Le pipeline d'upload conserve le format d'origine (pas de
        // ré-encodage transparent en WebP) — un PNG reste un PNG.
        self::assertSame('image/png', $headers->get('Content-Type'));
        self::assertSame((string) $asset->sizeBytes(), $headers->get('Content-Length'));
        $cacheControl = (string) $headers->get('Cache-Control');
        self::assertStringContainsString('public', $cacheControl);
        self::assertStringContainsString('max-age=3600', $cacheControl);
        // `must-revalidate` est OBLIGATOIRE : l'accessibilité publique du
        // média est mutable (un article référençant peut être archivé), donc
        // un cache navigateur/CDN doit revalider auprès de l'origine dès que
        // le max-age expire, sans jamais servir de réponse périmée.
        self::assertStringContainsString('must-revalidate', $cacheControl);
        // `immutable` est INTERDIT : il autoriserait un client à réutiliser
        // un 200 en cache indéfiniment sans revalidation, ce qui violerait
        // le scénario « article archivé → 404 » (le média reste servi côté
        // client alors que la porte SQL le refuse désormais).
        self::assertStringNotContainsString('immutable', $cacheControl);
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        // Les médias publics accompagnent un contenu SEO — on autorise l'indexation.
        self::assertSame('index, follow', $headers->get('X-Robots-Tag'));

        // ETag fort (pas `W/`) — le contenu est byte-à-byte stable (sha256).
        $etag = (string) $headers->get('ETag');
        self::assertMatchesRegularExpression('/^"[a-f0-9]+"$/', $etag);
        self::assertStringStartsNotWith('W/', $etag);

        // Le corps streamé doit être un PNG valide (magic bytes 0x89PNG).
        $body = $this->client->getInternalResponse()->getContent();
        self::assertStringStartsWith("\x89PNG", $body);
        self::assertSame($asset->sizeBytes(), \strlen($body));
    }

    public function testIfNoneMatchReturns304WithoutBody(): void
    {
        $asset = $this->uploadRealPng();
        $this->attachAsHero($asset, publish: true);

        // Premier appel : on récupère l'ETag.
        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122());
        self::assertResponseIsSuccessful();
        $etag = (string) $this->client->getResponse()->headers->get('ETag');
        self::assertNotSame('', $etag);

        // Second appel avec `If-None-Match` → 304 sans corps.
        $this->client->request(
            'GET',
            '/media/' . $asset->id()->toRfc4122(),
            server: ['HTTP_IF_NONE_MATCH' => $etag],
        );

        self::assertResponseStatusCodeSame(304);
        self::assertSame('', $this->client->getInternalResponse()->getContent());
        self::assertNotNull($this->client->getResponse()->headers->get('X-Request-Id'));
    }

    public function testArchivingSoleReferencingArticleFlipsMediaTo404(): void
    {
        // Justifie le refus de `Cache-Control: immutable` : l'accessibilité
        // publique d'un média n'est PAS immuable — elle dépend de l'existence
        // d'au moins un article Published qui le référence.
        $asset = $this->uploadRealPng();
        $article = $this->attachAsHero($asset, publish: true);

        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122());
        self::assertResponseIsSuccessful();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article->archive(new \DateTimeImmutable('2026-08-03T11:00:00+00:00'));
        $em->flush();
        $em->clear();

        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122());
        self::assertResponseStatusCodeSame(404);
    }

    public function testSharedMediaRemains200WhileOnePublishedArticleReferencesIt(): void
    {
        // Contrat « médias partagés » : tant qu'au moins un article Published
        // référence le média, la route sert 200 — l'archivage d'un article
        // parmi plusieurs n'invalide pas la diffusion.
        $asset = $this->uploadRealPng();
        $articleA = $this->attachAsHero($asset, publish: true, slug: 'article-shared-a');
        $articleB = $this->attachAsHero($asset, publish: true, slug: 'article-shared-b');

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $articleA->archive(new \DateTimeImmutable('2026-08-03T11:00:00+00:00'));
        $em->flush();
        $em->clear();

        $this->client->request('GET', '/media/' . $asset->id()->toRfc4122());
        self::assertResponseIsSuccessful();
    }

    public function testIfModifiedSinceRecentReturns304(): void
    {
        $asset = $this->uploadRealPng();
        $this->attachAsHero($asset, publish: true);

        // Une date largement postérieure à `createdAt` du média : le
        // navigateur affirme « j'ai déjà cette version depuis longtemps ».
        $future = (new \DateTimeImmutable('now'))
            ->modify('+1 day')
            ->format(\DateTimeInterface::RFC7231);

        $this->client->request(
            'GET',
            '/media/' . $asset->id()->toRfc4122(),
            server: ['HTTP_IF_MODIFIED_SINCE' => $future],
        );

        self::assertResponseStatusCodeSame(304);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Uploade un vrai PNG via le pipeline complet — le handler normalise
     * en WebP, calcule les dimensions/sha256, persiste le MediaAsset et
     * pose le fichier physique dans le storage local du test.
     */
    private function uploadRealPng(): MediaAsset
    {
        $path = tempnam(sys_get_temp_dir(), 'public-media-src-');
        self::assertNotFalse($path);
        $this->tempFiles[] = $path;

        $image = imagecreatetruecolor(240, 160);
        self::assertNotFalse($image);
        $c = imagecolorallocate($image, 30, 60, 90);
        self::assertNotFalse($c);
        imagefilledrectangle($image, 0, 0, 239, 159, $c);
        imagepng($image, $path, 6);
        imagedestroy($image);

        $handler = self::getContainer()->get(UploadEditorialMediaHandler::class);
        $result = $handler(new UploadEditorialMedia(
            sourcePath: $path,
            originalFilename: 'public-media.png',
        ));

        return $result->asset;
    }

    /**
     * Renvoie l'unique asset persisté (utilisé par le test « orphelin »
     * qui n'a pas besoin de garder la référence entre étapes).
     */
    private function lastAssetId(): Uuid
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $conn = $em->getConnection();
        $row = $conn->fetchAssociative('SELECT id FROM editorial_media_asset LIMIT 1');
        self::assertIsArray($row);

        return Uuid::fromString((string) $row['id']);
    }

    /**
     * Attache l'asset comme image principale d'un article, publié ou non
     * selon le paramètre. On flushe pour que la porte SQL voie la ligne.
     * Renvoie l'agrégat pour permettre aux tests de lifecycle
     * (archive/restore) de piloter la suite sans re-fetch.
     */
    private function attachAsHero(MediaAsset $asset, bool $publish, ?string $slug = null): \App\Editorial\Domain\Article
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $articleRepo = self::getContainer()->get(DoctrineArticleRepository::class);

        $builder = (new ArticleBuilder())
            ->withSlug($slug ?? ($publish ? 'article-public-media' : 'brouillon-media'))
            ->withHeroImage(ArticleHeroImage::create($asset->id(), 'Alt éditorial de test.'));

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
