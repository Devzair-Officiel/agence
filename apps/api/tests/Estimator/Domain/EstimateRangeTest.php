<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\EstimateRange;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\Money;
use PHPUnit\Framework\TestCase;

/**
 * Ces valeurs sont des fixtures de test et ne représentent pas les tarifs Devzair.
 */
final class EstimateRangeTest extends TestCase
{
    public function testCreatesValidRange(): void
    {
        $min   = Money::of(100000, 'EUR');
        $max   = Money::of(200000, 'EUR');
        $range = EstimateRange::create($min, $max);

        self::assertTrue($range->minimum->equals($min));
        self::assertTrue($range->maximum->equals($max));
    }

    public function testAcceptsEqualMinAndMax(): void
    {
        $amount = Money::of(150000, 'EUR');
        $range  = EstimateRange::create($amount, $amount);

        self::assertSame('EUR', $range->currency());
    }

    public function testRejectsMinGreaterThanMax(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        EstimateRange::create(Money::of(200000, 'EUR'), Money::of(100000, 'EUR'));
    }

    public function testRejectsMixedCurrencies(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        EstimateRange::create(Money::of(100000, 'EUR'), Money::of(200000, 'USD'));
    }

    public function testCurrencyDelegatesToMinimum(): void
    {
        $range = EstimateRange::create(Money::of(0, 'EUR'), Money::of(50000, 'EUR'));

        self::assertSame('EUR', $range->currency());
    }

    public function testEquals(): void
    {
        $r1 = EstimateRange::create(Money::of(100, 'EUR'), Money::of(200, 'EUR'));
        $r2 = EstimateRange::create(Money::of(100, 'EUR'), Money::of(200, 'EUR'));
        $r3 = EstimateRange::create(Money::of(100, 'EUR'), Money::of(300, 'EUR'));

        self::assertTrue($r1->equals($r2));
        self::assertFalse($r1->equals($r3));
    }
}
