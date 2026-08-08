<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use App\EditorialMedia\Domain\Sha256;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Sha256Test extends TestCase
{
    public function testNormalizesToLowercase(): void
    {
        $hex = str_repeat('AB', 32);

        $hash = Sha256::fromString($hex);

        self::assertSame(str_repeat('ab', 32), $hash->toString());
    }

    public function testTrimsSurroundingWhitespace(): void
    {
        $hex = str_repeat('a', 64);

        $hash = Sha256::fromString(" \n" . $hex . " \t");

        self::assertSame($hex, $hash->toString());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidProvider(): iterable
    {
        yield 'too short' => [str_repeat('a', 63)];
        yield 'too long' => [str_repeat('a', 65)];
        yield 'non hex' => [str_repeat('g', 64)];
        yield 'empty' => [''];
        yield 'with spaces inside' => [str_repeat('a', 32) . ' ' . str_repeat('b', 31)];
    }

    #[DataProvider('invalidProvider')]
    public function testRejectsMalformed(string $raw): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);

        Sha256::fromString($raw);
    }
}
