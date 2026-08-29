<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\EstimateAssumption;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use PHPUnit\Framework\TestCase;

final class EstimateAssumptionTest extends TestCase
{
    public function testCreatesFromCode(): void
    {
        $a = EstimateAssumption::fromCode('ASSUMPTION_CONTENT_PROVIDED');

        self::assertSame('ASSUMPTION_CONTENT_PROVIDED', $a->code());
        self::assertSame('ASSUMPTION_CONTENT_PROVIDED', (string) $a);
    }

    public function testTrimsWhitespace(): void
    {
        $a = EstimateAssumption::fromCode('  ASSUMPTION_SCALE_SMALL  ');

        self::assertSame('ASSUMPTION_SCALE_SMALL', $a->code());
    }

    public function testRejectsEmptyCode(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        EstimateAssumption::fromCode('');
    }

    public function testRejectsWhitespaceOnly(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        EstimateAssumption::fromCode('   ');
    }

    public function testEquals(): void
    {
        $a1 = EstimateAssumption::fromCode('ASSUMPTION_A');
        $a2 = EstimateAssumption::fromCode('ASSUMPTION_A');
        $a3 = EstimateAssumption::fromCode('ASSUMPTION_B');

        self::assertTrue($a1->equals($a2));
        self::assertFalse($a1->equals($a3));
    }
}
