<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Domain;

use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ArticleSlugTest extends TestCase
{
    public function testAcceptsValidSlug(): void
    {
        $slug = ArticleSlug::fromString('publier-un-article-editorial');

        self::assertSame('publier-un-article-editorial', $slug->value());
        self::assertSame('publier-un-article-editorial', (string) $slug);
    }

    public function testTrimsSurroundingWhitespace(): void
    {
        $slug = ArticleSlug::fromString('  publier-un-article  ');

        self::assertSame('publier-un-article', $slug->value());
    }

    public function testEqualsWhenSameValue(): void
    {
        $a = ArticleSlug::fromString('conseil-agence');
        $b = ArticleSlug::fromString('conseil-agence');

        self::assertTrue($a->equals($b));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidSlugProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'too short' => ['ab'];
        yield 'uppercase' => ['Article-Editorial'];
        yield 'leading dash' => ['-article'];
        yield 'trailing dash' => ['article-'];
        yield 'double dash' => ['article--edito'];
        yield 'spaces' => ['article edito'];
        yield 'special chars' => ['article_edito'];
        yield 'accents' => ['éditorial'];
    }

    #[DataProvider('invalidSlugProvider')]
    public function testRejectsInvalidSlug(string $raw): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        ArticleSlug::fromString($raw);
    }

    public function testRejectsTooLongSlug(): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        ArticleSlug::fromString(str_repeat('a', 121));
    }
}
