<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Application\Policy;

use App\EditorialMedia\Application\Exception\ImageDimensionsExceededException;
use App\EditorialMedia\Application\Exception\MediaTooLargeException;
use App\EditorialMedia\Application\Exception\UnsupportedMediaTypeException;
use App\EditorialMedia\Application\Policy\MediaUploadPolicy;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MediaUploadPolicyTest extends TestCase
{
    private MediaUploadPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new MediaUploadPolicy();
    }

    public function testAcceptsJpegPngWebp(): void
    {
        self::assertSame(MediaType::Jpeg, $this->policy->assertMimeAccepted('image/jpeg'));
        self::assertSame(MediaType::Png, $this->policy->assertMimeAccepted('image/png'));
        self::assertSame(MediaType::WebP, $this->policy->assertMimeAccepted('image/webp'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function rejectedMimeProvider(): iterable
    {
        yield 'svg' => ['image/svg+xml'];
        yield 'gif' => ['image/gif'];
        yield 'pdf' => ['application/pdf'];
        yield 'html' => ['text/html'];
        yield 'empty' => [''];
    }

    #[DataProvider('rejectedMimeProvider')]
    public function testRejectsUnsupportedMime(string $mime): void
    {
        $this->expectException(UnsupportedMediaTypeException::class);

        $this->policy->assertMimeAccepted($mime);
    }

    public function testAcceptsSizeUpToBoundary(): void
    {
        $this->policy->assertSizeAccepted(MediaUploadPolicy::MAX_SIZE_BYTES);
        $this->addToAssertionCount(1);
    }

    public function testRejectsSizeAboveBoundary(): void
    {
        $this->expectException(MediaTooLargeException::class);

        $this->policy->assertSizeAccepted(MediaUploadPolicy::MAX_SIZE_BYTES + 1);
    }

    public function testRejectsZeroSize(): void
    {
        $this->expectException(MediaTooLargeException::class);

        $this->policy->assertSizeAccepted(0);
    }

    public function testAcceptsDimensionsAtBoundary(): void
    {
        $this->policy->assertDimensionsAccepted(
            MediaDimensions::of(MediaUploadPolicy::MAX_WIDTH, 1),
        );
        $this->addToAssertionCount(1);
    }

    public function testRejectsExcessWidth(): void
    {
        $this->expectException(ImageDimensionsExceededException::class);

        $this->policy->assertDimensionsAccepted(
            MediaDimensions::of(MediaUploadPolicy::MAX_WIDTH + 1, 100),
        );
    }

    public function testRejectsExcessHeight(): void
    {
        $this->expectException(ImageDimensionsExceededException::class);

        $this->policy->assertDimensionsAccepted(
            MediaDimensions::of(100, MediaUploadPolicy::MAX_HEIGHT + 1),
        );
    }

    public function testRejectsExcessTotalPixelsWithinPerAxisBounds(): void
    {
        $this->expectException(ImageDimensionsExceededException::class);

        // 8000 * 5001 = 40 008 000 > MAX_TOTAL_PIXELS 40 000 000, chaque axe reste
        // sous sa borne dédiée — c'est bien le total qui doit refuser l'image.
        $this->policy->assertDimensionsAccepted(MediaDimensions::of(8000, 5001));
    }
}
