<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use PHPUnit\Framework\TestCase;

final class CareNeedTest extends TestCase
{
    public function testFromStringAcceptsValidValue(): void
    {
        self::assertSame(CareNeed::Maintenance, CareNeed::fromString('maintenance'));
    }

    public function testFromStringRejectsUnknownValue(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        CareNeed::fromString('hosting');
    }

    public function testFromListDeduplicates(): void
    {
        $result = CareNeed::fromList(['support', 'support', 'continuous_seo']);

        self::assertCount(2, $result);
    }

    public function testToListRoundTrips(): void
    {
        $needs = [CareNeed::FunctionalEvolution, CareNeed::ContentUpdates];
        $list  = CareNeed::toList($needs);

        self::assertSame(['functional_evolution', 'content_updates'], $list);
    }
}
