<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\ProjectFeature;
use PHPUnit\Framework\TestCase;

final class ProjectFeatureTest extends TestCase
{
    public function testFromStringAcceptsValidValue(): void
    {
        self::assertSame(ProjectFeature::Payment, ProjectFeature::fromString('payment'));
    }

    public function testFromStringRejectsUnknownValue(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        ProjectFeature::fromString('unknown_feature');
    }

    public function testFromListDeduplicates(): void
    {
        $result = ProjectFeature::fromList(['payment', 'payment', 'shipping']);

        self::assertCount(2, $result);
        self::assertSame(ProjectFeature::Payment, $result[0]);
        self::assertSame(ProjectFeature::Shipping, $result[1]);
    }

    public function testFromListRejectsInvalidEntry(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        ProjectFeature::fromList(['payment', 'bad_feature']);
    }

    public function testToListPreservesOrder(): void
    {
        $features = [ProjectFeature::Authentication, ProjectFeature::Notifications];
        $list     = ProjectFeature::toList($features);

        self::assertSame(['authentication', 'notifications'], $list);
    }

    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn(ProjectFeature $f) => $f->value, ProjectFeature::cases());
        self::assertSame(count($values), count(array_unique($values)));
    }
}
