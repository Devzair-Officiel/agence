<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\CreateDraftArticle;
use App\Editorial\Application\Command\CreateDraftArticleHandler;
use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleSlugAlreadyExistsException;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Infrastructure\Markdown\MarkdownContentValidator;
use App\Editorial\Infrastructure\Markdown\MarkdownSecurityPolicy;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;

final class CreateDraftArticleHandlerTest extends TestCase
{
    use EntityManagerStub;

    private function validator(): MarkdownContentValidator
    {
        return new MarkdownContentValidator(new MarkdownSecurityPolicy());
    }

    private function validCommand(string $slug = 'nouvel-article-brouillon'): CreateDraftArticle
    {
        return new CreateDraftArticle(
            slug: $slug,
            title: 'Un tout nouveau brouillon créé depuis l\'admin',
            excerpt: 'Un résumé volontairement long pour dépasser la borne minimale imposée par le domaine éditorial.',
            bodyMarkdown: "## Introduction\n\nCorps markdown parfaitement légitime, sans HTML brut ni schéma dangereux.\n",
            seoTitle: 'Titre SEO calibré pour la SERP française — Devzair',
            seoDescription: 'Description SEO parfaitement dimensionnée, sans dépasser la borne haute imposée par le domaine éditorial.',
            authorName: 'Devzair',
            authorType: AuthorType::Organization,
            expertises: [ExpertiseIdentifier::Concevoir],
        );
    }

    public function testCreatesDraftAndFlushesOnce(): void
    {
        $repository = new InMemoryArticleRepository();
        $handler = new CreateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $result = $handler($this->validCommand('article-tout-neuf'));

        self::assertSame('article-tout-neuf', $result->article->slug()->value());
        self::assertSame(ArticleStatus::Draft, $result->article->status());
        self::assertNull($result->article->publishedAt());
        self::assertSame(
            $result->article->id()->toRfc4122(),
            $repository->findById($result->article->id())?->id()->toRfc4122(),
        );
    }

    public function testRejectsInvalidSlugBeforeAnyPersist(): void
    {
        $repository = new InMemoryArticleRepository();
        $handler = new CreateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $this->expectException(ArticleInvariantViolation::class);

        $handler(new CreateDraftArticle(
            slug: 'Slug Invalide Majuscules',
            title: 'Titre parfaitement valide',
            excerpt: 'Un résumé volontairement long pour dépasser la borne minimale imposée par le domaine éditorial.',
            bodyMarkdown: "## Corps\n\nContenu ok.\n",
            seoTitle: 'Titre SEO calibré pour la SERP française — Devzair',
            seoDescription: 'Description SEO parfaitement dimensionnée, sans dépasser la borne haute imposée par le domaine éditorial.',
            authorName: 'Devzair',
            authorType: AuthorType::Organization,
            expertises: [ExpertiseIdentifier::Concevoir],
        ));
    }

    public function testRejectsMarkdownWithRawHtml(): void
    {
        $repository = new InMemoryArticleRepository();
        $handler = new CreateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $this->expectException(MarkdownValidationException::class);

        $handler(new CreateDraftArticle(
            slug: 'body-html-refuse',
            title: 'Titre parfaitement valide',
            excerpt: 'Un résumé volontairement long pour dépasser la borne minimale imposée par le domaine éditorial.',
            bodyMarkdown: "## Introduction\n\n<script>alert(1)</script>\n",
            seoTitle: 'Titre SEO calibré pour la SERP française — Devzair',
            seoDescription: 'Description SEO parfaitement dimensionnée, sans dépasser la borne haute imposée par le domaine éditorial.',
            authorName: 'Devzair',
            authorType: AuthorType::Organization,
            expertises: [ExpertiseIdentifier::Concevoir],
        ));
    }

    public function testRejectsDuplicateSlugBeforePersist(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('slug-deja-pris')->build());
        $handler = new CreateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $this->expectException(ArticleSlugAlreadyExistsException::class);
        $this->expectExceptionMessageMatches('/slug-deja-pris/');

        $handler($this->validCommand('slug-deja-pris'));
    }

    public function testAtomicityInvalidExpertiseKeepsRepositoryEmpty(): void
    {
        $repository = new InMemoryArticleRepository();
        $handler = new CreateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        try {
            $handler(new CreateDraftArticle(
                slug: 'atomique',
                title: 'Titre parfaitement valide',
                excerpt: 'Un résumé volontairement long pour dépasser la borne minimale imposée par le domaine éditorial.',
                bodyMarkdown: "## Corps\n\nOk.\n",
                seoTitle: 'Titre SEO calibré pour la SERP française — Devzair',
                seoDescription: 'Description SEO parfaitement dimensionnée, sans dépasser la borne haute imposée par le domaine éditorial.',
                authorName: 'Devzair',
                authorType: AuthorType::Organization,
                expertises: [],
            ));
            self::fail('Attendu : ArticleInvariantViolation.');
        } catch (ArticleInvariantViolation) {
            self::assertNull(
                $repository->findBySlug(\App\Editorial\Domain\ArticleSlug::fromString('atomique')),
                'Aucun article ne doit avoir été persisté en cas d\'échec de validation.',
            );
        }
    }

    public function testCreatesPersonAuthored(): void
    {
        $repository = new InMemoryArticleRepository();
        $handler = new CreateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $result = $handler(new CreateDraftArticle(
            slug: 'article-nominatif',
            title: 'Un article signé par une personne physique',
            excerpt: 'Un résumé volontairement long pour dépasser la borne minimale imposée par le domaine éditorial.',
            bodyMarkdown: "## Corps\n\nOk.\n",
            seoTitle: 'Titre SEO calibré pour la SERP française — Devzair',
            seoDescription: 'Description SEO parfaitement dimensionnée, sans dépasser la borne haute imposée par le domaine éditorial.',
            authorName: 'Aurélien Boudon',
            authorType: AuthorType::Person,
            expertises: [ExpertiseIdentifier::FaireEvoluer],
        ));

        self::assertSame(AuthorType::Person, $result->article->author()->type());
        self::assertSame('Aurélien Boudon', $result->article->author()->name());
    }
}
