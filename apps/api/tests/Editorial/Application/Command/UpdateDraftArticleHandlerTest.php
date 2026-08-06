<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\UpdateDraftArticle;
use App\Editorial\Application\Command\UpdateDraftArticleHandler;
use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotEditableException;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Infrastructure\Markdown\MarkdownContentValidator;
use App\Editorial\Infrastructure\Markdown\MarkdownSecurityPolicy;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UpdateDraftArticleHandlerTest extends TestCase
{
    use EntityManagerStub;

    private function validator(): MarkdownContentValidator
    {
        return new MarkdownContentValidator(new MarkdownSecurityPolicy());
    }

    public function testUpdatesTitleAndBumpsUpdatedAt(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())
                ->withId($id)
                ->withSlug('a-modifier')
                ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
                ->build(),
        );
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $result = $handler(new UpdateDraftArticle(
            $id,
            title: 'Nouveau titre plus explicite pour ce brouillon',
        ));

        self::assertTrue($result->mutated);
        self::assertSame('Nouveau titre plus explicite pour ce brouillon', $result->article->title());
    }

    public function testAppliesMultipleFieldsInOneFlush(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())
                ->withId($id)
                ->withSlug('multi-champs')
                ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
                ->build(),
        );
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $result = $handler(new UpdateDraftArticle(
            $id,
            title: 'Un nouveau titre parfaitement valide',
            excerpt: 'Un tout nouveau résumé assez long pour dépasser la borne minimale imposée par le domaine.',
            expertises: [ExpertiseIdentifier::Valoriser, ExpertiseIdentifier::Visibilite],
        ));

        self::assertTrue($result->mutated);
        self::assertSame('Un nouveau titre parfaitement valide', $result->article->title());
        self::assertSame(
            [ExpertiseIdentifier::Valoriser, ExpertiseIdentifier::Visibilite],
            $result->article->expertises(),
        );
    }

    public function testReturnsUnchangedWhenAllValuesIdentical(): void
    {
        $id = Uuid::v7();
        $article = (new ArticleBuilder())
            ->withId($id)
            ->withSlug('inchange')
            ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
            ->build();
        $originalUpdatedAt = $article->updatedAt();
        $repository = new InMemoryArticleRepository();
        $repository->save($article);
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $result = $handler(new UpdateDraftArticle(
            $id,
            title: $article->title(),
            excerpt: $article->excerpt(),
        ));

        self::assertFalse($result->mutated);
        self::assertSame(
            $originalUpdatedAt->getTimestamp(),
            $result->article->updatedAt()->getTimestamp(),
        );
    }

    public function testValidatesAllBeforeMutating(): void
    {
        $id = Uuid::v7();
        $article = (new ArticleBuilder())
            ->withId($id)
            ->withSlug('valid-avant-mutation')
            ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
            ->build();
        $originalTitle = $article->title();
        $originalUpdatedAt = $article->updatedAt();
        $repository = new InMemoryArticleRepository();
        $repository->save($article);
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        try {
            $handler(new UpdateDraftArticle(
                $id,
                title: 'Titre valide qui ne doit pas être appliqué',
                seoTitle: 'trop court',
                seoDescription: 'aussi trop court',
            ));
            self::fail('Expected ArticleInvariantViolation.');
        } catch (ArticleInvariantViolation) {
            self::assertSame(
                $originalTitle,
                $repository->findById($id)?->title(),
                'Le titre valide ne doit pas être appliqué si un autre champ est invalide (atomicité).',
            );
            self::assertSame(
                $originalUpdatedAt->getTimestamp(),
                $repository->findById($id)?->updatedAt()->getTimestamp(),
            );
        }
    }

    public function testRejectsMarkdownWithRawHtml(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withId($id)->withSlug('body-html')->build());
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $this->expectException(MarkdownValidationException::class);

        $handler(new UpdateDraftArticle(
            $id,
            bodyMarkdown: "## Titre\n\n<div>HTML brut interdit</div>\n",
        ));
    }

    public function testRejectsMarkdownExceedingSizeLimit(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withId($id)->withSlug('body-trop-gros')->build());
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('/trop volumineux/');

        $handler(new UpdateDraftArticle(
            $id,
            bodyMarkdown: str_repeat('a', MarkdownContentValidator::MAX_BODY_BYTES + 1),
        ));
    }

    public function testRejectsMutationOnPublishedArticle(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())->withId($id)->withSlug('publie-immuable')->published()->build(),
        );
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $this->expectException(ArticleNotEditableException::class);

        $handler(new UpdateDraftArticle(
            $id,
            title: 'Titre valide pour un article publié — refusé',
        ));
    }

    public function testRejectsSeoPartialProvided(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withId($id)->withSlug('seo-partiel')->build());
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $this->expectException(ArticleInvariantViolation::class);
        $this->expectExceptionMessageMatches('/seoTitle et seoDescription/');

        $handler(new UpdateDraftArticle(
            $id,
            seoTitle: 'Un nouveau titre SEO parfaitement dimensionné',
        ));
    }

    public function testRejectsAuthorPartialProvided(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withId($id)->withSlug('author-partiel')->build());
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $this->expectException(ArticleInvariantViolation::class);
        $this->expectExceptionMessageMatches('/authorName et authorType/');

        $handler(new UpdateDraftArticle(
            $id,
            authorType: AuthorType::Person,
        ));
    }

    public function testAcceptsBodyRewrite(): void
    {
        $id = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())
                ->withId($id)
                ->withSlug('body-reecrit')
                ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
                ->build(),
        );
        $handler = new UpdateDraftArticleHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->validator(),
        );

        $result = $handler(new UpdateDraftArticle(
            $id,
            bodyMarkdown: "## Nouveau corps\n\nRéécrit sans HTML.\n",
        ));

        self::assertTrue($result->mutated);
        self::assertStringContainsString('Nouveau corps', $result->article->bodyMarkdown());
    }

    public function testRaisesNotFoundForUnknownId(): void
    {
        $handler = new UpdateDraftArticleHandler(
            new InMemoryArticleRepository(),
            $this->entityManagerExpectingNoFlush(),
            new FixedClock(),
            $this->validator(),
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(new UpdateDraftArticle(Uuid::v7(), title: 'Titre valide pour cet article introuvable'));
    }
}
