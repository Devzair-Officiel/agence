<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use App\EditorialMedia\Domain\MediaType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MediaTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{string, MediaType}>
     */
    public static function acceptedMimeProvider(): iterable
    {
        yield 'jpeg' => ['image/jpeg', MediaType::Jpeg];
        yield 'jpeg uppercase' => ['IMAGE/JPEG', MediaType::Jpeg];
        yield 'png' => ['image/png', MediaType::Png];
        yield 'webp' => ['image/webp', MediaType::WebP];
        yield 'jpeg padded' => ["  image/jpeg\n", MediaType::Jpeg];
    }

    #[DataProvider('acceptedMimeProvider')]
    public function testTryFromMimeAcceptsSupportedTypes(string $mime, MediaType $expected): void
    {
        self::assertSame($expected, MediaType::tryFromMime($mime));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function rejectedMimeProvider(): iterable
    {
        yield 'svg' => ['image/svg+xml'];
        yield 'gif' => ['image/gif'];
        yield 'bmp' => ['image/bmp'];
        yield 'tiff' => ['image/tiff'];
        yield 'avif' => ['image/avif'];
        yield 'heic' => ['image/heic'];
        yield 'pdf' => ['application/pdf'];
        yield 'html' => ['text/html'];
        yield 'empty' => [''];
    }

    #[DataProvider('rejectedMimeProvider')]
    public function testTryFromMimeReturnsNullForUnsupported(string $mime): void
    {
        self::assertNull(MediaType::tryFromMime($mime));
    }

    public function testFromMimeThrowsOnUnsupported(): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);

        MediaType::fromMime('image/svg+xml');
    }

    public function testExtensionIsCanonicalPerType(): void
    {
        self::assertSame('jpg', MediaType::Jpeg->extension());
        self::assertSame('png', MediaType::Png->extension());
        self::assertSame('webp', MediaType::WebP->extension());
    }

    public function testAllowedMimesListIsComplete(): void
    {
        self::assertSame(
            ['image/jpeg', 'image/png', 'image/webp'],
            MediaType::allowedMimes(),
        );
    }
}
