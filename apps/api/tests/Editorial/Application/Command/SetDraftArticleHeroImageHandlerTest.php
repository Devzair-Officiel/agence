<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\SetDraftArticleHeroImage;
use App\Editorial\Application\Command\SetDraftArticleHeroImageHandler;
use App\Editorial\Application\Media\HeroMediaNotFoundException;
use App\Editorial\Application\Media\MediaAssetDescriptor;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotEditableException;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use App\Tests\Editorial\Support\InMemoryMediaAssetLookup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class SetDraftArticleHeroImageHandlerTest extends TestCase
{
    use EntityManagerStub;

    private function makeLookup(Uuid $mediaId): InMemoryMediaAssetLookup
    {
        $lookup = new InMemoryMediaAssetLookup();
        $lookup->register(new MediaAssetDescriptor(
            id: $mediaId,
            width: 1600,
            height: 900,
            mimeType: 'image/webp',
            sizeBytes: 240_000,
            sha256: str_repeat('a', 64),
        ));

        return $lookup;
    }

    public function testAttachesHeroImageToDraftAndFlushesOnce(): void
    {
        $articleId = Uuid::v7();
        $mediaId = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())
                ->withId($articleId)
                ->withSlug('draft-heroable')
                ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
                ->build(),
        );

        $handler = new SetDraftArticleHeroImageHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->makeLookup($mediaId),
        );

        $result = $handler(new SetDraftArticleHeroImage(
            articleId: $articleId,
            mediaAssetId: $mediaId,
            altText: 'Équipe collaborant sur un projet éditorial.',
        ));

        self::assertTrue($result->mutated);
        $hero = $result->article->heroImage();
        self::assertNotNull($hero);
        self::assertTrue($mediaId->equals($hero->mediaAssetId()));
        self::assertSame('Équipe collaborant sur un projet éditorial.', $hero->altText());
    }

    public function testReturnsUnchangedWhenSameMediaAndAlt(): void
    {
        $articleId = Uuid::v7();
        $mediaId = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $lookup = $this->makeLookup($mediaId);
        $article = (new ArticleBuilder())
            ->withId($articleId)
            ->withSlug('deja-heroed')
            ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
            ->build();
        // Attache d'abord une image via l'agrégat directement — pré-requis du test.
        $article->changeHeroImage(
            \App\Editorial\Domain\ArticleHeroImage::create($mediaId, 'Alt stable'),
            new \DateTimeImmutable('2026-08-02T09:00:00+00:00'),
        );
        $repository->save($article);
        $before = $article->updatedAt();

        $handler = new SetDraftArticleHeroImageHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-06T10:00:00+00:00'),
            $lookup,
        );

        $result = $handler(new SetDraftArticleHeroImage(
            articleId: $articleId,
            mediaAssetId: $mediaId,
            altText: 'Alt stable',
        ));

        self::assertFalse($result->mutated);
        self::assertSame($before->getTimestamp(), $result->article->updatedAt()->getTimestamp());
    }

    public function testRejectsWhenArticleNotFound(): void
    {
        $handler = new SetDraftArticleHeroImageHandler(
            new InMemoryArticleRepository(),
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->makeLookup(Uuid::v7()),
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(new SetDraftArticleHeroImage(
            articleId: Uuid::v7(),
            mediaAssetId: Uuid::v7(),
            altText: 'Alt',
        ));
    }

    public function testRejectsWhenMediaNotFoundAndLeavesArticleStrictlyUnchanged(): void
    {
        $articleId = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $article = (new ArticleBuilder())
            ->withId($articleId)
            ->withSlug('media-absent')
            ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
            ->build();
        $repository->save($article);
        $beforeUpdatedAt = $article->updatedAt();
        $beforeHero = $article->heroImage();

        $handler = new SetDraftArticleHeroImageHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            new InMemoryMediaAssetLookup(),
        );

        try {
            $handler(new SetDraftArticleHeroImage(
                articleId: $articleId,
                mediaAssetId: Uuid::v7(),
                altText: 'Alt',
            ));
            self::fail('HeroMediaNotFoundException attendue.');
        } catch (HeroMediaNotFoundException) {
            // ok
        }

        self::assertSame($beforeUpdatedAt->getTimestamp(), $article->updatedAt()->getTimestamp());
        self::assertSame($beforeHero, $article->heroImage());
    }

    public function testRejectsWhenAltIsEmpty(): void
    {
        $articleId = Uuid::v7();
        $mediaId = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())
                ->withId($articleId)
                ->withSlug('alt-empty')
                ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
                ->build(),
        );

        $handler = new SetDraftArticleHeroImageHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->makeLookup($mediaId),
        );

        $this->expectException(ArticleInvariantViolation::class);

        $handler(new SetDraftArticleHeroImage(
            articleId: $articleId,
            mediaAssetId: $mediaId,
            altText: '   ',
        ));
    }

    public function testRejectsWhenArticleIsPublished(): void
    {
        $articleId = Uuid::v7();
        $mediaId = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())
                ->withId($articleId)
                ->withSlug('publie-heroable')
                ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
                ->published()
                ->build(),
        );

        $handler = new SetDraftArticleHeroImageHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->makeLookup($mediaId),
        );

        $this->expectException(ArticleNotEditableException::class);

        $handler(new SetDraftArticleHeroImage(
            articleId: $articleId,
            mediaAssetId: $mediaId,
            altText: 'Alt valide.',
        ));
    }

    public function testRejectsWhenArticleIsArchived(): void
    {
        $articleId = Uuid::v7();
        $mediaId = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $article = (new ArticleBuilder())
            ->withId($articleId)
            ->withSlug('archived-heroable')
            ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
            ->published()
            ->build();
        $article->archive(new \DateTimeImmutable('2026-08-02T00:00:00+00:00'));
        $repository->save($article);

        $handler = new SetDraftArticleHeroImageHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
            $this->makeLookup($mediaId),
        );

        $this->expectException(ArticleNotEditableException::class);

        $handler(new SetDraftArticleHeroImage(
            articleId: $articleId,
            mediaAssetId: $mediaId,
            altText: 'Alt',
        ));
    }
}
