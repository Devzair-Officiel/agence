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
 * Bout-à-bout HTTP du retrait d'image principale (Phase 9B).
 *
 * Couverture :
 *   - POST-only : GET → 405 ;
 *   - CSRF invalide → redirection sans mutation ;
 *   - happy path : hero retiré + PRG 303 ;
 *   - no-op (aucun hero) : PRG 303 avec flash info ;
 *   - refus si article Published (invariant Draft-only).
 */
final class AdminArticleHeroImageRemoveControllerTest extends WebTestCase
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

    public function testGetIsNotAllowed(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('remove-get-forbidden');

        $this->client->request('GET', '/admin/articles/'.$article->id()->toRfc4122().'/image/remove');

        self::assertResponseStatusCodeSame(405);
    }

    public function testPostWithoutValidCsrfRedirectsWithoutMutation(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $media = $this->seedOneMedia();
        $article = $this->seedDraftWithHero('remove-csrf-invalid', $media);
        $id = $article->id()->toRfc4122();

        $this->client->request('POST', '/admin/articles/'.$id.'/image/remove', [
            '_csrf_token' => 'invalid-token',
        ]);

        self::assertResponseRedirects('/admin/articles/'.$id.'/edit', 303);
        self::assertNotNull(
            $this->reloadArticle($article->id())->heroImage(),
            'Un CSRF invalide ne doit jamais retirer l\'image.',
        );
    }

    public function testPostRemovesHeroImageAndRedirects(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $media = $this->seedOneMedia();
        $article = $this->seedDraftWithHero('remove-happy-path', $media);
        $id = $article->id()->toRfc4122();

        $token = $this->fetchRemoveToken($id);
        $this->client->request('POST', '/admin/articles/'.$id.'/image/remove', [
            '_csrf_token' => $token,
        ]);

        self::assertResponseRedirects('/admin/articles/'.$id.'/edit', 303);
        self::assertNull($this->reloadArticle($article->id())->heroImage());
    }

    public function testPostWithoutHeroImageIsNoop(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        // Pour capturer un jeton `remove` sans hero, on attache brièvement,
        // capture le token, puis on remet à null via le domaine.
        $media = $this->seedOneMedia();
        $article = $this->seedDraftWithHero('remove-noop', $media);
        $id = $article->id()->toRfc4122();
        $token = $this->fetchRemoveToken($id);

        // Retirer via le domaine avant de rejouer le POST — le handler doit
        // renvoyer un résultat non muté sans lever.
        $article->changeHeroImage(null, new \DateTimeImmutable('2026-08-08T13:00:00+00:00'));
        $this->persist($article);
        $updatedBefore = $this->reloadArticle($article->id())->updatedAt();

        $this->client->request('POST', '/admin/articles/'.$id.'/image/remove', [
            '_csrf_token' => $token,
        ]);

        self::assertResponseRedirects('/admin/articles/'.$id.'/edit', 303);
        self::assertSame(
            $updatedBefore->getTimestamp(),
            $this->reloadArticle($article->id())->updatedAt()->getTimestamp(),
        );
    }

    public function testPostOnPublishedArticleIsRefused(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $media = $this->seedOneMedia();
        // On construit le brouillon + image + capture du jeton, puis on publie
        // avant de rejouer la requête — le handler doit refuser (Draft-only)
        // et l'image reste rattachée.
        $article = $this->seedDraftWithHero('remove-published-refuse', $media);
        $id = $article->id()->toRfc4122();
        $token = $this->fetchRemoveToken($id);

        $article->publish(
            new \DateTimeImmutable('2026-08-08T14:00:00+00:00'),
            new \DateTimeImmutable('2026-08-08T14:00:00+00:00'),
        );
        $this->persist($article);

        $this->client->request('POST', '/admin/articles/'.$id.'/image/remove', [
            '_csrf_token' => $token,
        ]);

        self::assertResponseRedirects('/admin/articles/'.$id.'/edit', 303);
        self::assertNotNull(
            $this->reloadArticle($article->id())->heroImage(),
            'Un article publié doit conserver son image principale.',
        );
    }

    public function testInvalidUuidReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('POST', '/admin/articles/not-a-uuid/image/remove', [
            '_csrf_token' => 'whatever',
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    private function seedOneMedia(): MediaAsset
    {
        $repo = self::getContainer()->get(MediaAssetRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $asset = (new MediaAssetBuilder())
            ->withOriginalFilename('remove-target.jpg')
            ->build();
        $repo->save($asset);
        $em->flush();

        return $asset;
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

    private function seedDraftWithHero(string $slug, MediaAsset $media): Article
    {
        $article = (new ArticleBuilder())
            ->withSlug($slug)
            ->withNow(new \DateTimeImmutable('2026-08-08T12:00:00+00:00'))
            ->build();
        $article->changeHeroImage(
            ArticleHeroImage::create($media->id(), 'Alt existant.'),
            new \DateTimeImmutable('2026-08-08T12:05:00+00:00'),
        );
        $this->persist($article);

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

    private function fetchRemoveToken(string $id): string
    {
        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/edit');
        $node = $crawler->filterXPath(
            '//form[contains(@action, "/admin/articles/'.$id.'/image/remove")]//input[@name="_csrf_token"]',
        );
        \assert($node->count() > 0, 'Formulaire remove introuvable dans /edit.');
        $token = $node->attr('value');
        \assert(\is_string($token) && $token !== '');

        return $token;
    }
}
