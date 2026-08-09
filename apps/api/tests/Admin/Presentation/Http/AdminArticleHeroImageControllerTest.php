<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleHeroImage;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use App\Tests\EditorialMedia\Support\MediaAssetBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Bout-à-bout HTTP du chooser d'image principale (Phase 9B).
 *
 * Couverture :
 *   - firewall admin actif ;
 *   - GET rend 200 pour un brouillon, 409 sinon (chooser verrouillé) ;
 *   - pagination `page=N` conservée ;
 *   - POST valide → PRG 303 vers l'édition + hero persisté ;
 *   - POST idempotent (même média + même alt) → 303 sans bump `updatedAt` ;
 *   - POST CSRF invalide → 403 ;
 *   - POST média inconnu / manquant / alt vide → 422 avec message.
 */
final class AdminArticleHeroImageControllerTest extends WebTestCase
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

    public function testUnauthenticatedIsRedirectedToLogin(): void
    {
        $article = $this->seedDraft('unauth-hero');
        $this->client->request('GET', '/admin/articles/'.$article->id()->toRfc4122().'/image');

        self::assertResponseRedirects();
        self::assertStringContainsString(
            '/admin/login',
            (string) $this->client->getResponse()->headers->get('Location'),
        );
    }

    public function testGetChooserRendersForDraftWithMediaLibrary(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedMedia(3);
        $article = $this->seedDraft('hero-chooser-draft');

        $crawler = $this->client->request('GET', '/admin/articles/'.$article->id()->toRfc4122().'/image');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filterXPath('//input[@name="alt_text"]')->count());
        self::assertSame(3, $crawler->filterXPath('//input[@name="media_id" and @type="radio"]')->count());
    }

    public function testGetChooserReturns409ForPublishedArticle(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $article = (new ArticleBuilder())->withSlug('hero-published-locked')->published()->build();
        $repo->save($article);
        $em->flush();

        $crawler = $this->client->request('GET', '/admin/articles/'.$article->id()->toRfc4122().'/image');

        self::assertResponseStatusCodeSame(409);
        self::assertSame(0, $crawler->filterXPath('//input[@name="media_id"]')->count());
    }

    public function testPaginationSecondPageServesRemainingMedia(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedMedia(22);
        $article = $this->seedDraft('hero-pagination');

        $crawler = $this->client->request(
            'GET',
            '/admin/articles/'.$article->id()->toRfc4122().'/image?page=2',
        );

        self::assertResponseIsSuccessful();
        // 20 par page ; il reste 2 miniatures en page 2.
        self::assertSame(2, $crawler->filterXPath('//input[@name="media_id" and @type="radio"]')->count());
    }

    public function testPostWithoutValidCsrfReturns403(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $media = $this->seedOneMedia();
        $article = $this->seedDraft('hero-csrf-invalid');

        $this->client->request('POST', '/admin/articles/'.$article->id()->toRfc4122().'/image', [
            '_csrf_token' => 'invalid-token',
            'media_id' => $media->id()->toRfc4122(),
            'alt_text' => 'Alt valide.',
        ]);

        self::assertResponseStatusCodeSame(403);
        self::assertNull(
            $this->reloadArticle($article->id())->heroImage(),
            'Un CSRF invalide ne doit jamais persister l\'image.',
        );
    }

    public function testPostHappyPathSavesAndRedirectsToEdit(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $media = $this->seedOneMedia();
        $article = $this->seedDraft('hero-happy-path');
        $id = $article->id()->toRfc4122();

        $token = $this->fetchChooserToken($id);

        $this->client->request('POST', '/admin/articles/'.$id.'/image', [
            '_csrf_token' => $token,
            'media_id' => $media->id()->toRfc4122(),
            'alt_text' => 'Équipe collaborant devant un écran.',
        ]);

        self::assertResponseRedirects('/admin/articles/'.$id.'/edit', 303);
        $hero = $this->reloadArticle($article->id())->heroImage();
        self::assertNotNull($hero);
        self::assertTrue($media->id()->equals($hero->mediaAssetId()));
        self::assertSame('Équipe collaborant devant un écran.', $hero->altText());
    }

    public function testPostIdempotentDoesNotBumpUpdatedAt(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $media = $this->seedOneMedia();
        $article = $this->seedDraft('hero-idempotent');
        $article->changeHeroImage(
            ArticleHeroImage::create($media->id(), 'Alt stable'),
            new \DateTimeImmutable('2026-08-08T12:00:00+00:00'),
        );
        $this->persist($article);
        $updatedBefore = $this->reloadArticle($article->id())->updatedAt();
        $id = $article->id()->toRfc4122();

        $token = $this->fetchChooserToken($id);

        $this->client->request('POST', '/admin/articles/'.$id.'/image', [
            '_csrf_token' => $token,
            'media_id' => $media->id()->toRfc4122(),
            'alt_text' => 'Alt stable',
        ]);

        self::assertResponseRedirects('/admin/articles/'.$id.'/edit', 303);
        self::assertSame(
            $updatedBefore->getTimestamp(),
            $this->reloadArticle($article->id())->updatedAt()->getTimestamp(),
            'Un POST no-op ne doit pas bumper updatedAt.',
        );
    }

    public function testPostWithUnknownMediaReturns422(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedMedia(1);
        $article = $this->seedDraft('hero-unknown-media');
        $id = $article->id()->toRfc4122();

        $token = $this->fetchChooserToken($id);

        $this->client->request('POST', '/admin/articles/'.$id.'/image', [
            '_csrf_token' => $token,
            'media_id' => Uuid::v7()->toRfc4122(),
            'alt_text' => 'Alt valide.',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertNull($this->reloadArticle($article->id())->heroImage());
    }

    public function testPostWithMissingMediaReturns422(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('hero-missing-media');
        $id = $article->id()->toRfc4122();

        $token = $this->fetchChooserToken($id);

        $this->client->request('POST', '/admin/articles/'.$id.'/image', [
            '_csrf_token' => $token,
            'media_id' => '',
            'alt_text' => 'Alt valide.',
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testPostWithBlankAltReturns422(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $media = $this->seedOneMedia();
        $article = $this->seedDraft('hero-blank-alt');
        $id = $article->id()->toRfc4122();

        $token = $this->fetchChooserToken($id);

        $this->client->request('POST', '/admin/articles/'.$id.'/image', [
            '_csrf_token' => $token,
            'media_id' => $media->id()->toRfc4122(),
            'alt_text' => '   ',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertNull($this->reloadArticle($article->id())->heroImage());
    }

    public function testInvalidUuidReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/articles/not-a-uuid/image');

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @return list<MediaAsset>
     */
    private function seedMedia(int $count): array
    {
        $repo = self::getContainer()->get(MediaAssetRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $out = [];
        for ($i = 1; $i <= $count; ++$i) {
            $asset = (new MediaAssetBuilder())
                ->withOriginalFilename(\sprintf('hero-%02d.jpg', $i))
                ->withCreatedAt(\sprintf('2026-08-%02dT10:%02d:00+00:00', 1 + intdiv($i, 24), $i % 60))
                ->build();
            $repo->save($asset);
            $out[] = $asset;
        }
        $em->flush();

        return $out;
    }

    private function seedOneMedia(): MediaAsset
    {
        return $this->seedMedia(1)[0];
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

    private function persist(Article $article): void
    {
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo->save($article);
        $em->flush();
    }

    private function reloadArticle(Uuid $id): Article
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $article = $repo->findById($id);
        \assert($article instanceof Article, \sprintf('Article %s introuvable.', $id->toRfc4122()));

        return $article;
    }

    private function fetchChooserToken(string $id): string
    {
        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/image');
        $node = $crawler->filterXPath('//form[contains(@class, "hero-image-form")]//input[@name="_csrf_token"]');
        \assert($node->count() > 0, 'Formulaire chooser introuvable.');
        $token = $node->attr('value');
        \assert(\is_string($token) && $token !== '');

        return $token;
    }
}
