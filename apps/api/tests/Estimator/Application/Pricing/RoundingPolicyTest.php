<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Application\Pricing;

use App\Estimator\Application\Pricing\RoundingPolicy;
use PHPUnit\Framework\TestCase;

final class RoundingPolicyTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('roundMinProvider')]
    public function testRoundMinConservative(int $input, int $expected): void
    {
        self::assertSame($expected, RoundingPolicy::roundMinConservative($input));
    }

    public static function roundMinProvider(): iterable
    {
        yield 'already multiple of 5000'     => [90_000, 90_000];
        yield 'rounds down to nearest 5000'  => [103_500, 100_000];
        yield 'rounds down — 113 500'        => [113_500, 110_000];
        yield 'exactly on boundary'          => [100_000, 100_000];
        yield 'one cent over boundary'       => [100_001, 100_000];
        yield 'four thousand nine-ninety-nine' => [104_999, 100_000];
        yield 'zero stays zero'              => [0, 0];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roundMaxProvider')]
    public function testRoundMaxConservative(int $input, int $expected): void
    {
        self::assertSame($expected, RoundingPolicy::roundMaxConservative($input));
    }

    public static function roundMaxProvider(): iterable
    {
        yield 'already multiple of 5000'    => [130_000, 130_000];
        yield 'rounds up to nearest 5000'   => [156_000, 160_000];
        yield 'rounds up — 191 000'         => [191_000, 195_000];
        yield 'exactly on boundary'         => [200_000, 200_000];
        yield 'one cent over boundary'      => [200_001, 205_000];
        yield 'zero stays zero'             => [0, 0];
    }

    public function testMinNeverExceedsMax(): void
    {
        $rawMin = 90_000;
        $rawMax = 130_000;

        $roundedMin = RoundingPolicy::roundMinConservative($rawMin);
        $roundedMax = RoundingPolicy::roundMaxConservative($rawMax);

        self::assertLessThanOrEqual($roundedMax, $roundedMin);
    }

    public function testRoundingNeverNarrowsTheRange(): void
    {
        // roundMin must be <= rawMin, roundMax must be >= rawMax
        $cases = [
            [103_500, 156_000],
            [117_000, 188_500],
            [250_000, 400_000],
        ];

        foreach ($cases as [$rawMin, $rawMax]) {
            self::assertLessThanOrEqual($rawMin, RoundingPolicy::roundMinConservative($rawMin));
            self::assertGreaterThanOrEqual($rawMax, RoundingPolicy::roundMaxConservative($rawMax));
        }
    }
}
