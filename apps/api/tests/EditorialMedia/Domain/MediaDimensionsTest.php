<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use App\EditorialMedia\Domain\MediaDimensions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MediaDimensionsTest extends TestCase
{
    public function testAcceptsPositiveDimensions(): void
    {
        $dim = MediaDimensions::of(1920, 1080);

        self::assertSame(1920, $dim->width());
        self::assertSame(1080, $dim->height());
        self::assertSame(2_073_600, $dim->totalPixels());
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function invalidProvider(): iterable
    {
        yield 'zero width' => [0, 100];
        yield 'zero height' => [100, 0];
        yield 'negative width' => [-1, 100];
        yield 'negative height' => [100, -1];
        yield 'both zero' => [0, 0];
    }

    #[DataProvider('invalidProvider')]
    public function testRejectsNonPositive(int $width, int $height): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);

        MediaDimensions::of($width, $height);
    }
}
