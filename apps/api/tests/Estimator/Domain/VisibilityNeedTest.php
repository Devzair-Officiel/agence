<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\VisibilityNeed;
use PHPUnit\Framework\TestCase;

final class VisibilityNeedTest extends TestCase
{
    public function testFromStringAcceptsValidValue(): void
    {
        self::assertSame(VisibilityNeed::LocalVisibility, VisibilityNeed::fromString('local_visibility'));
    }

    public function testFromStringRejectsUnknownValue(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        VisibilityNeed::fromString('paid_ads');
    }

    public function testFromListDeduplicates(): void
    {
        $result = VisibilityNeed::fromList(['seo_basic', 'seo_basic']);

        self::assertCount(1, $result);
        self::assertSame(VisibilityNeed::SeoBasic, $result[0]);
    }

    public function testToListRoundTrips(): void
    {
        $needs = [VisibilityNeed::SeoAdvanced, VisibilityNeed::EditorialStrategy];
        $list  = VisibilityNeed::toList($needs);

        self::assertSame(['seo_advanced', 'editorial_strategy'], $list);
    }
}
