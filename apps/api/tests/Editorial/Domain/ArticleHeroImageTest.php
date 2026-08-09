<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Domain;

use App\Editorial\Domain\ArticleHeroImage;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ArticleHeroImageTest extends TestCase
{
    public function testCreateAcceptsValidAlt(): void
    {
        $mediaId = Uuid::v7();
        $vo = ArticleHeroImage::create($mediaId, 'Deux personnes travaillant devant un écran.');

        self::assertTrue($mediaId->equals($vo->mediaAssetId()));
        self::assertSame('Deux personnes travaillant devant un écran.', $vo->altText());
    }

    public function testCreateTrimsSurroundingWhitespace(): void
    {
        $vo = ArticleHeroImage::create(Uuid::v7(), "   Illustration principale.   \n");

        self::assertSame('Illustration principale.', $vo->altText());
    }

    public function testCreateRejectsEmptyAlt(): void
    {
        $this->expectException(ArticleInvariantViolation::class);
        $this->expectExceptionMessage('obligatoire');

        ArticleHeroImage::create(Uuid::v7(), '');
    }

    public function testCreateRejectsWhitespaceOnlyAlt(): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        ArticleHeroImage::create(Uuid::v7(), "   \t\n  ");
    }

    public function testCreateRejectsAltAboveMaxLength(): void
    {
        $this->expectException(ArticleInvariantViolation::class);
        $this->expectExceptionMessage('300');

        ArticleHeroImage::create(Uuid::v7(), str_repeat('a', 301));
    }

    public function testCreateAcceptsAltAtExactMaxLength(): void
    {
        $vo = ArticleHeroImage::create(Uuid::v7(), str_repeat('a', 300));

        self::assertSame(300, mb_strlen($vo->altText()));
    }

    public function testEqualsReturnsTrueForSameMediaAndAlt(): void
    {
        $id = Uuid::v7();
        $a = ArticleHeroImage::create($id, 'Alt commun');
        $b = ArticleHeroImage::create($id, 'Alt commun');

        self::assertTrue($a->equals($b));
        self::assertTrue($b->equals($a));
    }

    public function testEqualsReturnsFalseWhenAltDiffers(): void
    {
        $id = Uuid::v7();
        $a = ArticleHeroImage::create($id, 'Alt A');
        $b = ArticleHeroImage::create($id, 'Alt B');

        self::assertFalse($a->equals($b));
    }

    public function testEqualsReturnsFalseWhenMediaDiffers(): void
    {
        $a = ArticleHeroImage::create(Uuid::v7(), 'Alt');
        $b = ArticleHeroImage::create(Uuid::v7(), 'Alt');

        self::assertFalse($a->equals($b));
    }
}
