<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Query;

use App\Editorial\Application\Query\GetAdminArticlePreview;
use App\Editorial\Application\Query\GetAdminArticlePreviewHandler;
use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Author;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Domain\SeoMetadata;
use App\Editorial\Infrastructure\Markdown\CommonMarkArticleRenderer;
use App\Editorial\Infrastructure\Markdown\MarkdownSecurityPolicy;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\InMemoryAdminArticleReadRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Unit tests du handler de prévisualisation admin (Phase 8C4).
 *
 * Couvre les cas fonctionnels (Draft / Published / Archived, absent),
 * les invariants de sécurité (non-mutation, non-persistance, HTML
 * échappé / balises dangereuses retirées) et l'inclusion complète des
 * métadonnées éditoriales dans la vue rendue.
 */
final class GetAdminArticlePreviewHandlerTest extends TestCase
{
    private CommonMarkArticleRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new CommonMarkArticleRenderer(new MarkdownSecurityPolicy());
    }

    public function testReturnsPreviewForDraftArticle(): void
    {
        $id = Uuid::v7();
        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save(
            (new ArticleBuilder())
                ->withId($id)
                ->withSlug('preview-draft-alpha')
                ->withTitle('Titre du brouillon prévisualisé')
                ->build(),
        );

        $handler = new GetAdminArticlePreviewHandler($repo, $this->renderer);
        $preview = $handler(new GetAdminArticlePreview($id));

        self::assertSame($id->toRfc4122(), $preview->id);
        self::assertSame('preview-draft-alpha', $preview->slug);
        self::assertSame(ArticleStatus::Draft, $preview->status);
        self::assertNull($preview->publishedAt);
        self::assertStringContainsString('<h2>Introduction</h2>', $preview->contentHtml);
        // La vue expose bien les dates originales (immuable → identiques après render).
        self::assertNotNull($preview->createdAt);
        self::assertNotNull($preview->updatedAt);
    }

    public function testReturnsPreviewForPublishedArticle(): void
    {
        $id = Uuid::v7();
        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save(
            (new ArticleBuilder())
                ->withId($id)
                ->withSlug('preview-published')
                ->published()
                ->build(),
        );

        $handler = new GetAdminArticlePreviewHandler($repo, $this->renderer);
        $preview = $handler(new GetAdminArticlePreview($id));

        self::assertSame(ArticleStatus::Published, $preview->status);
        self::assertNotNull($preview->publishedAt);
    }

    public function testReturnsPreviewForArchivedArticle(): void
    {
        $id = Uuid::v7();
        $article = (new ArticleBuilder())
            ->withId($id)
            ->withSlug('preview-archived')
            ->published()
            ->build();
        $article->archive(new \DateTimeImmutable('2026-08-04T10:00:00+00:00'));

        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save($article);

        $handler = new GetAdminArticlePreviewHandler($repo, $this->renderer);
        $preview = $handler(new GetAdminArticlePreview($id));

        self::assertSame(ArticleStatus::Archived, $preview->status);
        // Archivage préserve la date historique de publication — le rédacteur
        // doit pouvoir la voir dans la prévisualisation admin.
        self::assertNotNull($preview->publishedAt);
    }

    public function testRaisesNotFoundForUnknownId(): void
    {
        $handler = new GetAdminArticlePreviewHandler(
            new InMemoryAdminArticleReadRepository(),
            $this->renderer,
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(new GetAdminArticlePreview(Uuid::v7()));
    }

    public function testHandlerDoesNotMutateArticle(): void
    {
        $id = Uuid::v7();
        $article = (new ArticleBuilder())
            ->withId($id)
            ->withSlug('preview-non-mutating')
            ->build();

        $originalUpdatedAt = $article->updatedAt();
        $originalStatus = $article->status();
        $originalBody = $article->bodyMarkdown();
        $originalTitle = $article->title();

        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save($article);

        $handler = new GetAdminArticlePreviewHandler($repo, $this->renderer);
        $handler(new GetAdminArticlePreview($id));

        // Aucune méthode de mutation n'a dû être invoquée : les getters
        // renvoient exactement l'état initial.
        self::assertEquals($originalUpdatedAt, $article->updatedAt());
        self::assertSame($originalStatus, $article->status());
        self::assertSame($originalBody, $article->bodyMarkdown());
        self::assertSame($originalTitle, $article->title());
    }

    public function testPortInterfaceHasNoWriteMethodsExposedToHandler(): void
    {
        // Garantie structurelle : le port `AdminArticleReadRepositoryInterface`
        // n'expose que des méthodes de lecture (`paginate`, `count`,
        // `findForEdit`). Le handler ne pourrait pas persister même s'il
        // essayait — c'est une vérification par introspection.
        $reflection = new \ReflectionClass(\App\Editorial\Application\Query\AdminArticleReadRepositoryInterface::class);
        $writeMethodPrefixes = ['save', 'persist', 'remove', 'flush', 'update', 'delete', 'insert', 'store'];

        foreach ($reflection->getMethods() as $method) {
            foreach ($writeMethodPrefixes as $prefix) {
                self::assertStringStartsNotWith(
                    $prefix,
                    strtolower($method->getName()),
                    \sprintf(
                        'Le port admin ne doit exposer aucune méthode d\'écriture — trouvé « %s ».',
                        $method->getName(),
                    ),
                );
            }
        }
    }

    public function testRawHtmlInMarkdownIsStrippedNotRendered(): void
    {
        // Défense en profondeur : même si un import a laissé passer du HTML
        // (ne devrait jamais arriver — `MarkdownContentValidator` refuse à
        // l'entrée), le renderer sanitize à la sortie.
        $id = Uuid::v7();
        $article = Article::createDraft(
            $id,
            ArticleSlug::fromString('preview-xss-attempt'),
            'Titre valide pour la prévisualisation XSS',
            'Chapô assez long pour dépasser la borne minimale imposée par le domaine.',
            "Intro paragraphe.\n\n<script>alert('xss')</script>\n\n<iframe src=\"https://evil.example\"></iframe>\n\nFin.",
            SeoMetadata::create(
                'Titre SEO XSS de la prévisualisation admin',
                'Description SEO assez longue pour être valide dans le domaine éditorial admin.',
            ),
            Author::organization('Devzair'),
            [ExpertiseIdentifier::Concevoir],
            new \DateTimeImmutable('2026-08-05T10:00:00+00:00'),
        );

        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save($article);

        $handler = new GetAdminArticlePreviewHandler($repo, $this->renderer);
        $preview = $handler(new GetAdminArticlePreview($id));

        // Le script et l'iframe doivent avoir été strippés (html_input=strip).
        self::assertStringNotContainsString('<script>', $preview->contentHtml);
        self::assertStringNotContainsString('</script>', $preview->contentHtml);
        self::assertStringNotContainsString('<iframe', $preview->contentHtml);
        self::assertStringNotContainsString('alert(', $preview->contentHtml);
    }

    public function testDangerousLinksAreNeutralized(): void
    {
        $id = Uuid::v7();
        $article = Article::createDraft(
            $id,
            ArticleSlug::fromString('preview-dangerous-links'),
            'Titre valide pour la prévisualisation dangerous links',
            'Chapô assez long pour dépasser la borne minimale imposée par le domaine.',
            "Voir [ce lien](javascript:alert(1)) ou [cette image](data:text/html,<h1>xss</h1>).",
            SeoMetadata::create(
                'Titre SEO dangerous links de la prévisualisation',
                'Description SEO assez longue pour être valide dans le domaine éditorial admin.',
            ),
            Author::organization('Devzair'),
            [ExpertiseIdentifier::Concevoir],
            new \DateTimeImmutable('2026-08-05T10:00:00+00:00'),
        );

        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save($article);

        $handler = new GetAdminArticlePreviewHandler($repo, $this->renderer);
        $preview = $handler(new GetAdminArticlePreview($id));

        // `allow_unsafe_links=false` neutralise l'attribut href pour les
        // schémas javascript: / data: — pas de href exécutable dans le rendu.
        self::assertStringNotContainsString('href="javascript:', $preview->contentHtml);
        self::assertStringNotContainsString('href="data:', $preview->contentHtml);
    }

    public function testExposesEditorialMetadataForAuditingBeforePublish(): void
    {
        $id = Uuid::v7();
        $article = (new ArticleBuilder())
            ->withId($id)
            ->withSlug('preview-full-metadata')
            ->withExpertises([ExpertiseIdentifier::Concevoir, ExpertiseIdentifier::Construire])
            ->build();

        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save($article);

        $handler = new GetAdminArticlePreviewHandler($repo, $this->renderer);
        $preview = $handler(new GetAdminArticlePreview($id));

        self::assertSame('preview-full-metadata', $preview->slug);
        self::assertNotSame('', $preview->seoTitle);
        self::assertNotSame('', $preview->seoDescription);
        self::assertNotSame('', $preview->authorName);
        self::assertCount(2, $preview->expertises);
    }
}
