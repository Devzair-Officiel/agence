<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\EstimateLineItem;
use App\Estimator\Domain\EstimateRange;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\LineItemCategory;
use App\Estimator\Domain\Money;
use PHPUnit\Framework\TestCase;

/**
 * Ces valeurs sont des fixtures de test et ne représentent pas les tarifs Devzair.
 */
final class EstimateLineItemTest extends TestCase
{
    private function makeRange(): EstimateRange
    {
        return EstimateRange::create(Money::of(100000, 'EUR'), Money::of(200000, 'EUR'));
    }

    public function testCreatesOneOffItem(): void
    {
        $item = EstimateLineItem::create('DESIGN', $this->makeRange(), LineItemCategory::OneOff);

        self::assertSame('DESIGN', $item->code);
        self::assertTrue($item->isOneOff());
        self::assertFalse($item->isRecurring());
    }

    public function testCreatesRecurringItem(): void
    {
        $item = EstimateLineItem::create('MAINTENANCE', $this->makeRange(), LineItemCategory::Recurring);

        self::assertTrue($item->isRecurring());
        self::assertFalse($item->isOneOff());
    }

    public function testTrimsCode(): void
    {
        $item = EstimateLineItem::create('  SEO  ', $this->makeRange(), LineItemCategory::OneOff);

        self::assertSame('SEO', $item->code);
    }

    public function testRejectsEmptyCode(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        EstimateLineItem::create('', $this->makeRange(), LineItemCategory::OneOff);
    }

    public function testRejectsWhitespaceOnlyCode(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        EstimateLineItem::create('   ', $this->makeRange(), LineItemCategory::Recurring);
    }
}
