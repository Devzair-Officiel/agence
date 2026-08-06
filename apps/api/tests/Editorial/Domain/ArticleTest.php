<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Domain;

use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Author;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotEditableException;
use App\Editorial\Domain\Exception\InvalidArticleTransitionException;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Domain\SeoMetadata;
use App\Tests\Editorial\Support\ArticleBuilder;
use PHPUnit\Framework\TestCase;

final class ArticleTest extends TestCase
{
    public function testDraftHasNoPublishedAt(): void
    {
        $article = (new ArticleBuilder())->build();

        self::assertSame(ArticleStatus::Draft, $article->status());
        self::assertNull($article->publishedAt());
    }

    public function testPublishSetsStatusAndTimestamp(): void
    {
        $now = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->build();

        $article->publish($now, $now);

        self::assertSame(ArticleStatus::Published, $article->status());
        self::assertNotNull($article->publishedAt());
        self::assertSame($now->getTimestamp(), $article->publishedAt()?->getTimestamp());
        self::assertTrue($article->status()->isPublished());
    }

    public function testPublishAcceptsExplicitPastPublishedAt(): void
    {
        $publishedAt = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $now = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->build();

        $article->publish($publishedAt, $now);

        self::assertSame($publishedAt->getTimestamp(), $article->publishedAt()?->getTimestamp());
        self::assertSame($now->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testPublishRefusesFuturePublishedAt(): void
    {
        $now = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->build();

        $this->expectException(ArticleInvariantViolation::class);

        $article->publish($now->modify('+1 second'), $now);
    }

    public function testPublishIsIdempotent(): void
    {
        $now = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->build();

        $article->publish($now, $now);
        $firstPublishedAt = $article->publishedAt();

        $later = $now->modify('+1 day');
        $article->publish($later, $later);

        self::assertSame(
            $firstPublishedAt?->getTimestamp(),
            $article->publishedAt()?->getTimestamp(),
        );
    }

    public function testArchiveClearsExposedStatus(): void
    {
        $article = (new ArticleBuilder())->published()->build();
        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));

        self::assertSame(ArticleStatus::Archived, $article->status());
        self::assertFalse($article->status()->isPublished());
    }

    public function testArchiveKeepsPublishedAt(): void
    {
        $publishedAt = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->build();
        $article->publish($publishedAt, $publishedAt);
        $capturedPublishedAt = $article->publishedAt();

        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));

        self::assertSame(ArticleStatus::Archived, $article->status());
        self::assertNotNull($article->publishedAt());
        self::assertSame(
            $capturedPublishedAt?->getTimestamp(),
            $article->publishedAt()?->getTimestamp(),
            'Un article archivé doit conserver sa date historique de publication.',
        );
    }

    public function testRequiresAtLeastOneExpertise(): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        (new ArticleBuilder())->withExpertises([])->build();
    }

    public function testDeduplicatesExpertises(): void
    {
        $article = (new ArticleBuilder())
            ->withExpertises([
                ExpertiseIdentifier::Concevoir,
                ExpertiseIdentifier::Concevoir,
                ExpertiseIdentifier::Valoriser,
            ])
            ->build();

        self::assertSame(
            [ExpertiseIdentifier::Concevoir, ExpertiseIdentifier::Valoriser],
            $article->expertises(),
        );
    }

    public function testRejectsEmptyBody(): void
    {
        $builder = new ArticleBuilder();
        $reflection = new \ReflectionProperty($builder, 'body');
        $reflection->setAccessible(true);
        $reflection->setValue($builder, '   ');

        $this->expectException(ArticleInvariantViolation::class);

        $builder->build();
    }

    public function testUpdatedAtEqualsCreatedAtOnConstruction(): void
    {
        $now = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($now)->build();

        self::assertSame(
            $article->createdAt()->getTimestamp(),
            $article->updatedAt()->getTimestamp(),
        );
    }

    public function testPublishBumpsUpdatedAt(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $published = new \DateTimeImmutable('2026-08-04T09:00:00+00:00');

        $article = (new ArticleBuilder())->withNow($created)->build();
        $article->publish($published, $published);

        self::assertSame($published->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeTitleUpdatesValueAndTimestamp(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $now = new \DateTimeImmutable('2026-08-05T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();

        $article->changeTitle('Nouveau titre plus explicite pour ce brouillon', $now);

        self::assertSame('Nouveau titre plus explicite pour ce brouillon', $article->title());
        self::assertSame($now->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeTitleIsNoOpWhenIdentical(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $originalTitle = $article->title();
        $originalUpdatedAt = $article->updatedAt();

        $article->changeTitle($originalTitle, new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));

        self::assertSame($originalTitle, $article->title());
        self::assertSame(
            $originalUpdatedAt->getTimestamp(),
            $article->updatedAt()->getTimestamp(),
            'Une mutation avec valeur identique ne doit pas bumper updatedAt.',
        );
    }

    public function testChangeTitleRejectsTooShort(): void
    {
        $article = (new ArticleBuilder())->build();

        $this->expectException(ArticleInvariantViolation::class);

        $article->changeTitle('abc', new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));
    }

    public function testChangeTitleRejectsWhenPublished(): void
    {
        $article = (new ArticleBuilder())->published()->build();

        $this->expectException(ArticleNotEditableException::class);

        $article->changeTitle('Un titre parfaitement valide', new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));
    }

    public function testChangeExcerptUpdatesValueAndTimestamp(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $now = new \DateTimeImmutable('2026-08-05T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $newExcerpt = 'Un nouveau résumé suffisamment long pour dépasser la borne minimale imposée par le domaine.';

        $article->changeExcerpt($newExcerpt, $now);

        self::assertSame($newExcerpt, $article->excerpt());
        self::assertSame($now->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeExcerptRejectsTooShort(): void
    {
        $article = (new ArticleBuilder())->build();

        $this->expectException(ArticleInvariantViolation::class);

        $article->changeExcerpt('trop court', new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));
    }

    public function testChangeExcerptRejectsWhenArchived(): void
    {
        $article = (new ArticleBuilder())->published()->build();
        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));

        $this->expectException(ArticleNotEditableException::class);

        $article->changeExcerpt(
            'Un résumé suffisamment long pour dépasser la borne minimale imposée par le domaine.',
            new \DateTimeImmutable('2026-08-06T00:00:00+00:00'),
        );
    }

    public function testRewriteBodyUpdatesValueAndTimestamp(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $now = new \DateTimeImmutable('2026-08-05T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();

        $article->rewriteBody("## Nouveau corps\n\nRéécrit complètement.", $now);

        self::assertStringContainsString('Nouveau corps', $article->bodyMarkdown());
        self::assertSame($now->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testRewriteBodyRejectsEmpty(): void
    {
        $article = (new ArticleBuilder())->build();

        $this->expectException(ArticleInvariantViolation::class);

        $article->rewriteBody("   \n\t  ", new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));
    }

    public function testRewriteBodyIsNoOpWhenIdentical(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $originalBody = $article->bodyMarkdown();
        $originalUpdatedAt = $article->updatedAt();

        $article->rewriteBody($originalBody, new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));

        self::assertSame($originalUpdatedAt->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeSeoUpdatesBothFields(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $now = new \DateTimeImmutable('2026-08-05T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $seo = SeoMetadata::create(
            'Nouveau titre SEO adapté au format SERP moderne',
            'Nouvelle description SEO — assez longue pour passer les bornes minimales et rester crédible pour le domaine.',
        );

        $article->changeSeo($seo, $now);

        self::assertSame($seo->title(), $article->seo()->title());
        self::assertSame($seo->description(), $article->seo()->description());
        self::assertSame($now->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeSeoIsNoOpWhenIdentical(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $originalUpdatedAt = $article->updatedAt();
        $sameSeo = SeoMetadata::create($article->seo()->title(), $article->seo()->description());

        $article->changeSeo($sameSeo, new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));

        self::assertSame($originalUpdatedAt->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeAuthorUpdatesValue(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $now = new \DateTimeImmutable('2026-08-05T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $newAuthor = Author::person('Aïcha Redjeb');

        $article->changeAuthor($newAuthor, $now);

        self::assertTrue($article->author()->equals($newAuthor));
        self::assertSame($now->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeAuthorIsNoOpWhenIdentical(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $originalUpdatedAt = $article->updatedAt();

        $article->changeAuthor(Author::organization('Devzair'), new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));

        self::assertSame($originalUpdatedAt->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeExpertisesUpdatesValue(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $now = new \DateTimeImmutable('2026-08-05T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();

        $article->changeExpertises(
            [ExpertiseIdentifier::Valoriser, ExpertiseIdentifier::Visibilite],
            $now,
        );

        self::assertSame(
            [ExpertiseIdentifier::Valoriser, ExpertiseIdentifier::Visibilite],
            $article->expertises(),
        );
        self::assertSame($now->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeExpertisesRejectsEmpty(): void
    {
        $article = (new ArticleBuilder())->build();

        $this->expectException(ArticleInvariantViolation::class);

        $article->changeExpertises([], new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));
    }

    public function testChangeExpertisesIsNoOpWhenIdenticalAfterDedupe(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $originalUpdatedAt = $article->updatedAt();

        $article->changeExpertises(
            [ExpertiseIdentifier::Concevoir, ExpertiseIdentifier::Concevoir],
            new \DateTimeImmutable('2026-08-05T10:00:00+00:00'),
        );

        self::assertSame($originalUpdatedAt->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testMutationRefusesBackwardClock(): void
    {
        $created = new \DateTimeImmutable('2026-08-05T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();

        $this->expectException(ArticleInvariantViolation::class);

        $article->changeTitle(
            'Titre différent totalement valide',
            new \DateTimeImmutable('2026-08-05T08:59:59+00:00'),
        );
    }

    public function testRestoreArchivedResetsToDraft(): void
    {
        $article = (new ArticleBuilder())->published()->build();
        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));
        self::assertNotNull($article->publishedAt(), 'Sanity : archived article still keeps publishedAt.');

        $now = new \DateTimeImmutable('2026-08-06T00:00:00+00:00');
        $article->restore($now);

        self::assertSame(ArticleStatus::Draft, $article->status());
        self::assertNull($article->publishedAt(), 'restore() doit remettre publishedAt à null.');
        self::assertSame($now->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testRestoreDraftIsNoOp(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $originalUpdatedAt = $article->updatedAt();

        $article->restore(new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));

        self::assertSame(ArticleStatus::Draft, $article->status());
        self::assertSame($originalUpdatedAt->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testRestoreRefusesWhenPublished(): void
    {
        $article = (new ArticleBuilder())->published()->build();

        $this->expectException(InvalidArticleTransitionException::class);

        $article->restore(new \DateTimeImmutable('2026-08-05T10:00:00+00:00'));
    }

    public function testRestoredArticleIsEditableAgain(): void
    {
        $article = (new ArticleBuilder())->published()->build();
        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));
        $article->restore(new \DateTimeImmutable('2026-08-06T00:00:00+00:00'));

        $article->changeTitle(
            'Titre modifié après restauration du brouillon',
            new \DateTimeImmutable('2026-08-06T01:00:00+00:00'),
        );

        self::assertSame('Titre modifié après restauration du brouillon', $article->title());
    }
}
