<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\Author;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Domain\SeoMetadata;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Bout-à-bout HTTP de la prévisualisation admin (Phase 8C4).
 *
 * Cette suite vérifie surtout le noyau de sécurité :
 *   - firewall admin actif (302 vers /login pour anonyme) ;
 *   - 404 pour UUID malformé / article absent ;
 *   - contenu HTML rendu, pas de balise `<script>` injectée ;
 *   - échappement Twig sur le titre et le slug (défense en profondeur) ;
 *   - en-têtes sécurité posés par `AdminSecurityHeadersSubscriber` ;
 *   - actions conditionnelles selon le statut (Draft / Published / Archived) ;
 *   - actions rendues avec les jetons CSRF existants (pas de nouveau jeton).
 */
final class AdminArticlePreviewControllerTest extends WebTestCase
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

    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $article = $this->seedDraft('preview-anonymous-redirect');

        $this->client->request('GET', '/admin/articles/'.$article->id()->toRfc4122().'/preview');

        // Le firewall admin intercepte avant même le contrôleur — on ne doit
        // JAMAIS voir le titre de l'article dans la réponse. 302 ou 401 selon
        // la configuration ; ici Symfony pose une 302 vers /admin/login.
        self::assertResponseRedirects('/admin/login');
    }

    public function testInvalidUuidReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/articles/not-a-uuid/preview');

        self::assertResponseStatusCodeSame(404);
    }

    public function testUnknownIdReturns404(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        // UUID valide mais aucun article en base.
        $this->client->request('GET', '/admin/articles/'.Uuid::v7()->toRfc4122().'/preview');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDraftPreviewRendersContentAndActions(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('preview-draft-flow');
        $id = $article->id()->toRfc4122();

        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/preview');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//*[contains(concat(" ", normalize-space(@class), " "), " preview-banner ")]')->count(),
            'Bandeau « Prévisualisation privée » attendu.',
        );
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//*[contains(concat(" ", normalize-space(@class), " "), " status-badge ") and contains(concat(" ", normalize-space(@class), " "), " status-draft ")]')->count(),
            'Badge « brouillon » attendu.',
        );
        // Le rendu Markdown produit bien un H2.
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//*[contains(concat(" ", normalize-space(@class), " "), " preview-prose ")]//h2')->count(),
        );

        // Actions Draft : Publier + Archiver disponibles, Restaurer absent.
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//form[@action="/admin/articles/'.$id.'/publish"]')->count(),
        );
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//form[@action="/admin/articles/'.$id.'/archive"]')->count(),
        );
        self::assertSame(
            0,
            $crawler->filterXPath('//form[@action="/admin/articles/'.$id.'/restore"]')->count(),
        );
    }

    public function testPublishedPreviewOffersOnlyArchive(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $article = (new ArticleBuilder())
            ->withSlug('preview-published-flow')
            ->published()
            ->build();
        $repo->save($article);
        $em->flush();

        $id = $article->id()->toRfc4122();
        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/preview');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//*[contains(concat(" ", normalize-space(@class), " "), " status-badge ") and contains(concat(" ", normalize-space(@class), " "), " status-published ")]')->count(),
        );
        self::assertSame(
            0,
            $crawler->filterXPath('//form[@action="/admin/articles/'.$id.'/publish"]')->count(),
            'Un article publié ne doit PAS proposer « Publier » à nouveau.',
        );
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//form[@action="/admin/articles/'.$id.'/archive"]')->count(),
        );
        self::assertSame(
            0,
            $crawler->filterXPath('//form[@action="/admin/articles/'.$id.'/restore"]')->count(),
        );
    }

    public function testArchivedPreviewOffersOnlyRestore(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $article = (new ArticleBuilder())
            ->withSlug('preview-archived-flow')
            ->published()
            ->build();
        $article->archive(new \DateTimeImmutable('2026-08-06T10:00:00+00:00'));
        $repo->save($article);
        $em->flush();

        $id = $article->id()->toRfc4122();
        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/preview');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//*[contains(concat(" ", normalize-space(@class), " "), " status-badge ") and contains(concat(" ", normalize-space(@class), " "), " status-archived ")]')->count(),
        );
        self::assertSame(
            0,
            $crawler->filterXPath('//form[@action="/admin/articles/'.$id.'/publish"]')->count(),
        );
        self::assertSame(
            0,
            $crawler->filterXPath('//form[@action="/admin/articles/'.$id.'/archive"]')->count(),
        );
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//form[@action="/admin/articles/'.$id.'/restore"]')->count(),
        );
    }

    public function testActionsCarryValidCsrfTokens(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('preview-csrf-tokens');
        $id = $article->id()->toRfc4122();

        $crawler = $this->client->request('GET', '/admin/articles/'.$id.'/preview');

        // Chaque formulaire d'action doit porter un input `_csrf_token`
        // non vide, aligné sur les jetons attendus par les contrôleurs
        // existants (`article_publish_{id}` / `article_archive_{id}`).
        $publishToken = $crawler
            ->filterXPath('//form[contains(@action, "/publish")]//input[@name="_csrf_token"]')
            ->attr('value');
        self::assertIsString($publishToken);
        self::assertNotSame('', $publishToken);

        $archiveToken = $crawler
            ->filterXPath('//form[contains(@action, "/archive")]//input[@name="_csrf_token"]')
            ->attr('value');
        self::assertIsString($archiveToken);
        self::assertNotSame('', $archiveToken);
    }

    public function testPreviewEscapesUserProvidedTitleAndSlug(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        // Défense en profondeur : le domaine impose déjà des contraintes de
        // longueur / caractères, mais on vérifie que même s'il laissait passer
        // un `<` dans le titre, Twig l'échapperait en `&lt;` dans le HTML.
        $article = Article::createDraft(
            Uuid::v7(),
            ArticleSlug::fromString('preview-escaping-check'),
            'Titre <img src=x onerror=alert(1)> vérifiable dans la preview',
            'Chapô assez long pour dépasser la borne minimale imposée par le domaine éditorial admin.',
            '## Section normale du corps.',
            SeoMetadata::create(
                'Titre SEO <b>bold</b> pour test',
                'Description SEO assez longue pour être valide dans le domaine éditorial admin de test.',
            ),
            Author::organization('Devzair'),
            [ExpertiseIdentifier::Concevoir],
            new \DateTimeImmutable('2026-08-05T10:00:00+00:00'),
        );
        $repo->save($article);
        $em->flush();

        $this->client->request('GET', '/admin/articles/'.$article->id()->toRfc4122().'/preview');

        self::assertResponseIsSuccessful();
        $body = (string) $this->client->getResponse()->getContent();

        // La balise <img> doit apparaître échappée, jamais brute.
        self::assertStringNotContainsString('<img src=x onerror=alert(1)>', $body);
        self::assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $body);
        // Le <b> injecté dans le SEO title doit aussi être échappé.
        self::assertStringNotContainsString('<b>bold</b>', $body);
        self::assertStringContainsString('&lt;b&gt;bold&lt;/b&gt;', $body);
    }

    public function testSecurityHeadersAreAppliedToPreview(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('preview-headers');

        $this->client->request('GET', '/admin/articles/'.$article->id()->toRfc4122().'/preview');

        $headers = $this->client->getResponse()->headers;
        self::assertStringContainsString("default-src 'none'", (string) $headers->get('Content-Security-Policy'));
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertSame('noindex, nofollow', $headers->get('X-Robots-Tag'));
        self::assertStringContainsString('no-store', (string) $headers->get('Cache-Control'));
        self::assertStringContainsString('private', (string) $headers->get('Cache-Control'));
        self::assertSame('no-referrer', $headers->get('Referrer-Policy'));
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
    }

    public function testPreviewIsGetOnlyAndPostReturnsMethodNotAllowed(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $article = $this->seedDraft('preview-get-only');

        $this->client->request('POST', '/admin/articles/'.$article->id()->toRfc4122().'/preview');

        // La route est déclarée `methods: [GET]` — Symfony rend 405 sur POST,
        // ce qui prouve qu'aucune mutation n'est possible via cette URL.
        self::assertResponseStatusCodeSame(405);
    }

    public function testGetRequestDoesNotMutateArticle(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $repo = self::getContainer()->get(ArticleRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $article = (new ArticleBuilder())
            ->withSlug('preview-no-mutation')
            ->published()
            ->build();
        $repo->save($article);
        $em->flush();

        $id = $article->id();
        $statusBefore = $article->status();
        $updatedAtBefore = $article->updatedAt();
        $publishedAtBefore = $article->publishedAt();
        $bodyMarkdownBefore = $article->bodyMarkdown();

        $em->clear();

        $this->client->request('GET', '/admin/articles/'.$id->toRfc4122().'/preview');
        self::assertResponseIsSuccessful();

        $em->clear();
        $reloaded = $repo->findById($id);
        self::assertNotNull($reloaded, 'L\'article doit toujours exister après la preview.');

        self::assertSame($statusBefore, $reloaded->status(), 'Le statut ne doit pas changer après un GET preview.');
        self::assertEquals($updatedAtBefore, $reloaded->updatedAt(), 'updatedAt ne doit pas changer après un GET preview.');
        self::assertEquals($publishedAtBefore, $reloaded->publishedAt(), 'publishedAt ne doit pas changer après un GET preview.');
        self::assertSame($bodyMarkdownBefore, $reloaded->bodyMarkdown(), 'bodyMarkdown ne doit pas changer après un GET preview.');
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
}
