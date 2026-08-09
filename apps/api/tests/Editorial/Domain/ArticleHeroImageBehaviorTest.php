<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Domain;

use App\Editorial\Domain\ArticleHeroImage;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotEditableException;
use App\Tests\Editorial\Support\ArticleBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ArticleHeroImageBehaviorTest extends TestCase
{
    public function testDraftHasNoHeroImageByDefault(): void
    {
        $article = (new ArticleBuilder())->build();

        self::assertNull($article->heroImage());
    }

    public function testChangeHeroImageAssignsVoAndBumpsUpdatedAt(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $now = new \DateTimeImmutable('2026-08-05T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $mediaId = Uuid::v7();

        $article->changeHeroImage(ArticleHeroImage::create($mediaId, 'Alt significatif'), $now);

        $hero = $article->heroImage();
        self::assertNotNull($hero);
        self::assertTrue($mediaId->equals($hero->mediaAssetId()));
        self::assertSame('Alt significatif', $hero->altText());
        self::assertSame($now->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeHeroImageRemovesWhenNullPassed(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $set = new \DateTimeImmutable('2026-08-02T10:00:00+00:00');
        $unset = new \DateTimeImmutable('2026-08-03T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $article->changeHeroImage(ArticleHeroImage::create(Uuid::v7(), 'Alt'), $set);

        $article->changeHeroImage(null, $unset);

        self::assertNull($article->heroImage());
        self::assertSame($unset->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeHeroImageIsNoOpWhenSameMediaAndAlt(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $set = new \DateTimeImmutable('2026-08-02T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $mediaId = Uuid::v7();
        $article->changeHeroImage(ArticleHeroImage::create($mediaId, 'Alt inchangé'), $set);
        $before = $article->updatedAt();

        $article->changeHeroImage(
            ArticleHeroImage::create($mediaId, 'Alt inchangé'),
            new \DateTimeImmutable('2026-08-06T10:00:00+00:00'),
        );

        self::assertSame($before->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeHeroImageIsNoOpWhenBothAreNull(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $before = $article->updatedAt();

        $article->changeHeroImage(null, new \DateTimeImmutable('2026-08-06T10:00:00+00:00'));

        self::assertNull($article->heroImage());
        self::assertSame($before->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeHeroImageUpdatesAltOnly(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $set = new \DateTimeImmutable('2026-08-02T10:00:00+00:00');
        $rename = new \DateTimeImmutable('2026-08-04T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $mediaId = Uuid::v7();
        $article->changeHeroImage(ArticleHeroImage::create($mediaId, 'Alt initial'), $set);

        $article->changeHeroImage(ArticleHeroImage::create($mediaId, 'Alt révisé'), $rename);

        $hero = $article->heroImage();
        self::assertNotNull($hero);
        self::assertSame('Alt révisé', $hero->altText());
        self::assertTrue($mediaId->equals($hero->mediaAssetId()));
        self::assertSame($rename->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeHeroImageReplacesMediaKeepingAlt(): void
    {
        $created = new \DateTimeImmutable('2026-08-01T09:00:00+00:00');
        $set = new \DateTimeImmutable('2026-08-02T10:00:00+00:00');
        $replace = new \DateTimeImmutable('2026-08-04T10:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();
        $article->changeHeroImage(ArticleHeroImage::create(Uuid::v7(), 'Alt stable'), $set);

        $newMedia = Uuid::v7();
        $article->changeHeroImage(ArticleHeroImage::create($newMedia, 'Alt stable'), $replace);

        $hero = $article->heroImage();
        self::assertNotNull($hero);
        self::assertTrue($newMedia->equals($hero->mediaAssetId()));
        self::assertSame($replace->getTimestamp(), $article->updatedAt()->getTimestamp());
    }

    public function testChangeHeroImageRefusesWhenPublished(): void
    {
        $article = (new ArticleBuilder())->published()->build();

        $this->expectException(ArticleNotEditableException::class);

        $article->changeHeroImage(
            ArticleHeroImage::create(Uuid::v7(), 'Alt'),
            new \DateTimeImmutable('2026-08-05T10:00:00+00:00'),
        );
    }

    public function testChangeHeroImageRefusesWhenArchived(): void
    {
        $article = (new ArticleBuilder())->published()->build();
        $article->archive(new \DateTimeImmutable('2026-08-05T00:00:00+00:00'));

        $this->expectException(ArticleNotEditableException::class);

        $article->changeHeroImage(
            ArticleHeroImage::create(Uuid::v7(), 'Alt'),
            new \DateTimeImmutable('2026-08-06T00:00:00+00:00'),
        );
    }

    public function testChangeHeroImageRefusesNonMonotonicNow(): void
    {
        $created = new \DateTimeImmutable('2026-08-05T09:00:00+00:00');
        $article = (new ArticleBuilder())->withNow($created)->build();

        $this->expectException(ArticleInvariantViolation::class);

        $article->changeHeroImage(
            ArticleHeroImage::create(Uuid::v7(), 'Alt'),
            new \DateTimeImmutable('2026-08-04T09:00:00+00:00'),
        );
    }
}
