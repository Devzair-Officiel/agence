<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\EstimateAssumption;
use App\Estimator\Domain\EstimateLineItem;
use App\Estimator\Domain\EstimateRange;
use App\Estimator\Domain\EstimateResult;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\LineItemCategory;
use App\Estimator\Domain\Money;
use App\Estimator\Domain\PricingVersion;
use App\Estimator\Domain\ProjectType;
use PHPUnit\Framework\TestCase;

/**
 * Ces valeurs sont des fixtures de test et ne représentent pas les tarifs Devzair.
 */
final class EstimateResultTest extends TestCase
{
    private function makeRange(string $currency = 'EUR'): EstimateRange
    {
        return EstimateRange::create(Money::of(300000, $currency), Money::of(500000, $currency));
    }

    private function makeMinimalResult(): EstimateResult
    {
        return new EstimateResult(
            recommendedProjectType: ProjectType::VitrineSite,
            estimateRange:          $this->makeRange(),
            oneOffItems:            [],
            recurringItems:         [],
            assumptions:            [],
            pricingVersion:         PricingVersion::fromString('test-fixture-v0'),
        );
    }

    public function testCreatesMinimalResult(): void
    {
        $result = $this->makeMinimalResult();

        self::assertSame(ProjectType::VitrineSite, $result->recommendedProjectType);
        self::assertFalse($result->hasRecurringServices());
        self::assertFalse($result->hasAssumptions());
    }

    public function testHasRecurringServicesTrueWhenItemsPresent(): void
    {
        $recurring = EstimateLineItem::create('MAINTENANCE', $this->makeRange(), LineItemCategory::Recurring);
        $result    = new EstimateResult(
            recommendedProjectType: ProjectType::Ecommerce,
            estimateRange:          $this->makeRange(),
            oneOffItems:            [],
            recurringItems:         [$recurring],
            assumptions:            [],
            pricingVersion:         PricingVersion::fromString('test-fixture-v0'),
        );

        self::assertTrue($result->hasRecurringServices());
    }

    public function testHasAssumptionsTrueWhenPresent(): void
    {
        $result = new EstimateResult(
            recommendedProjectType: ProjectType::BusinessApp,
            estimateRange:          $this->makeRange(),
            oneOffItems:            [],
            recurringItems:         [],
            assumptions:            [EstimateAssumption::fromCode('ASSUMPTION_STOCK_NOT_INCLUDED')],
            pricingVersion:         PricingVersion::fromString('test-fixture-v0'),
        );

        self::assertTrue($result->hasAssumptions());
        self::assertCount(1, $result->assumptions);
    }

    public function testPricingVersionIsTaggedOnResult(): void
    {
        $result = $this->makeMinimalResult();

        self::assertSame('test-fixture-v0', $result->pricingVersion->value());
    }

    public function testRefusesRecurringItemInOneOffItems(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);

        $badItem = EstimateLineItem::create('SEO', $this->makeRange(), LineItemCategory::Recurring);

        new EstimateResult(
            recommendedProjectType: ProjectType::VitrineSite,
            estimateRange:          $this->makeRange(),
            oneOffItems:            [$badItem],
            recurringItems:         [],
            assumptions:            [],
            pricingVersion:         PricingVersion::fromString('test-fixture-v0'),
        );
    }

    public function testRefusesOneOffItemInRecurringItems(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);

        $badItem = EstimateLineItem::create('DESIGN', $this->makeRange(), LineItemCategory::OneOff);

        new EstimateResult(
            recommendedProjectType: ProjectType::VitrineSite,
            estimateRange:          $this->makeRange(),
            oneOffItems:            [],
            recurringItems:         [$badItem],
            assumptions:            [],
            pricingVersion:         PricingVersion::fromString('test-fixture-v0'),
        );
    }

    public function testRefusesOneOffItemWithDifferentCurrencyThanTotal(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);

        $usdItem = EstimateLineItem::create('DEVELOPMENT', $this->makeRange('USD'), LineItemCategory::OneOff);

        new EstimateResult(
            recommendedProjectType: ProjectType::Ecommerce,
            estimateRange:          $this->makeRange('EUR'),
            oneOffItems:            [$usdItem],
            recurringItems:         [],
            assumptions:            [],
            pricingVersion:         PricingVersion::fromString('test-fixture-v0'),
        );
    }

    public function testRefusesRecurringItemWithDifferentCurrencyThanTotal(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);

        $usdItem = EstimateLineItem::create('MAINTENANCE', $this->makeRange('USD'), LineItemCategory::Recurring);

        new EstimateResult(
            recommendedProjectType: ProjectType::Ecommerce,
            estimateRange:          $this->makeRange('EUR'),
            oneOffItems:            [],
            recurringItems:         [$usdItem],
            assumptions:            [],
            pricingVersion:         PricingVersion::fromString('test-fixture-v0'),
        );
    }

    public function testAcceptsMultipleCoherentLines(): void
    {
        $oneOff1    = EstimateLineItem::create('DESIGN', $this->makeRange(), LineItemCategory::OneOff);
        $oneOff2    = EstimateLineItem::create('DEVELOPMENT', $this->makeRange(), LineItemCategory::OneOff);
        $recurring1 = EstimateLineItem::create('MAINTENANCE', $this->makeRange(), LineItemCategory::Recurring);
        $recurring2 = EstimateLineItem::create('SEO', $this->makeRange(), LineItemCategory::Recurring);

        $result = new EstimateResult(
            recommendedProjectType: ProjectType::Ecommerce,
            estimateRange:          $this->makeRange(),
            oneOffItems:            [$oneOff1, $oneOff2],
            recurringItems:         [$recurring1, $recurring2],
            assumptions:            [],
            pricingVersion:         PricingVersion::fromString('test-fixture-v0'),
        );

        self::assertCount(2, $result->oneOffItems);
        self::assertCount(2, $result->recurringItems);
        self::assertTrue($result->hasRecurringServices());
    }
}
