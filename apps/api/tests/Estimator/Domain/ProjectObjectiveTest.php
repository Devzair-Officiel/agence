<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\ProjectObjective;
use PHPUnit\Framework\TestCase;

final class ProjectObjectiveTest extends TestCase
{
    public function testFromStringAcceptsValidValue(): void
    {
        self::assertSame(ProjectObjective::SellOnline, ProjectObjective::fromString('sell_online'));
    }

    public function testFromStringRejectsUnknownValue(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        ProjectObjective::fromString('invent');
    }

    public function testFromListDeduplicates(): void
    {
        $result = ProjectObjective::fromList(['present_business', 'present_business', 'sell_online']);

        self::assertCount(2, $result);
        self::assertSame(ProjectObjective::PresentBusiness, $result[0]);
        self::assertSame(ProjectObjective::SellOnline, $result[1]);
    }

    public function testFromListRejectsInvalidEntry(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        ProjectObjective::fromList(['present_business', 'bad']);
    }

    public function testToListRoundTrips(): void
    {
        $objectives = [ProjectObjective::GenerateLeads, ProjectObjective::ImproveVisibility];
        $list       = ProjectObjective::toList($objectives);

        self::assertSame(['generate_leads', 'improve_visibility'], $list);
    }

    public function testFromListAcceptsEmptyArray(): void
    {
        self::assertSame([], ProjectObjective::fromList([]));
    }
}
