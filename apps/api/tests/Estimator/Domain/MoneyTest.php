<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\Money;
use PHPUnit\Framework\TestCase;

/**
 * Ces valeurs sont des fixtures de test et ne représentent pas les tarifs Devzair.
 */
final class MoneyTest extends TestCase
{
    public function testCreatesValidMoney(): void
    {
        $money = Money::of(100000, 'EUR');

        self::assertSame(100000, $money->amountMinor);
        self::assertSame('EUR', $money->currency);
    }

    public function testNormalizesLowercaseCurrency(): void
    {
        $money = Money::of(0, 'eur');

        self::assertSame('EUR', $money->currency);
    }

    public function testAcceptsZeroAmount(): void
    {
        $money = Money::of(0, 'EUR');

        self::assertSame(0, $money->amountMinor);
    }

    public function testRejectsNegativeAmount(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        Money::of(-1, 'EUR');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidCurrencyProvider')]
    public function testRejectsInvalidCurrencyCode(string $currency): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        Money::of(100, $currency);
    }

    public static function invalidCurrencyProvider(): iterable
    {
        yield 'empty'      => [''];
        yield 'too short'  => ['EU'];
        yield 'too long'   => ['EURO'];
        yield 'with digit' => ['EU1'];
    }

    public function testIsSameCurrency(): void
    {
        $a = Money::of(100, 'EUR');
        $b = Money::of(200, 'EUR');
        $c = Money::of(100, 'USD');

        self::assertTrue($a->isSameCurrency($b));
        self::assertFalse($a->isSameCurrency($c));
    }

    public function testEquals(): void
    {
        $a = Money::of(500, 'EUR');
        $b = Money::of(500, 'EUR');
        $c = Money::of(501, 'EUR');

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
    }
}
