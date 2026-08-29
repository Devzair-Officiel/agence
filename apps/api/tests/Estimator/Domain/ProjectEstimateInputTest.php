<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\CurrentSituation;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectObjective;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;
use App\Tests\Estimator\Support\EstimateInputBuilder;
use PHPUnit\Framework\TestCase;

final class ProjectEstimateInputTest extends TestCase
{
    public function testBuildsMinimalInput(): void
    {
        $input = (new EstimateInputBuilder())->build();

        self::assertSame(ProjectType::VitrineSite, $input->projectType);
        self::assertSame(CurrentSituation::None, $input->currentSituation);
        self::assertSame(ProjectScale::Small, $input->scale);
        self::assertSame([], $input->features);
        self::assertSame([], $input->contentNeeds);
        self::assertSame([], $input->visibilityNeeds);
        self::assertSame([], $input->careNeeds);
    }

    public function testDeduplicatesObjectives(): void
    {
        $input = (new EstimateInputBuilder())
            ->withObjectives([ProjectObjective::SellOnline, ProjectObjective::SellOnline, ProjectObjective::GenerateLeads])
            ->build();

        self::assertCount(2, $input->objectives);
        self::assertSame(ProjectObjective::SellOnline, $input->objectives[0]);
        self::assertSame(ProjectObjective::GenerateLeads, $input->objectives[1]);
    }

    public function testDeduplicatesFeatures(): void
    {
        $input = (new EstimateInputBuilder())
            ->withFeatures([ProjectFeature::Payment, ProjectFeature::Payment, ProjectFeature::Shipping])
            ->build();

        self::assertCount(2, $input->features);
    }

    public function testHasFeatureReturnsTrueWhenPresent(): void
    {
        $input = (new EstimateInputBuilder())
            ->withFeatures([ProjectFeature::Multilingual])
            ->build();

        self::assertTrue($input->hasFeature(ProjectFeature::Multilingual));
        self::assertFalse($input->hasFeature(ProjectFeature::Payment));
    }

    public function testHasContentNeedReturnsTrueWhenPresent(): void
    {
        $input = (new EstimateInputBuilder())
            ->withContentNeeds([ContentNeed::ContentToWrite])
            ->build();

        self::assertTrue($input->hasContentNeed(ContentNeed::ContentToWrite));
        self::assertFalse($input->hasContentNeed(ContentNeed::PhotographyNeeded));
    }

    public function testHasVisibilityNeedReturnsTrueWhenPresent(): void
    {
        $input = (new EstimateInputBuilder())
            ->withVisibilityNeeds([VisibilityNeed::LocalVisibility])
            ->build();

        self::assertTrue($input->hasVisibilityNeed(VisibilityNeed::LocalVisibility));
        self::assertFalse($input->hasVisibilityNeed(VisibilityNeed::SeoAdvanced));
    }

    public function testHasCareNeedReturnsTrueWhenPresent(): void
    {
        $input = (new EstimateInputBuilder())
            ->withCareNeeds([CareNeed::Support])
            ->build();

        self::assertTrue($input->hasCareNeed(CareNeed::Support));
        self::assertFalse($input->hasCareNeed(CareNeed::Maintenance));
    }

    public function testContainsNoPiiFields(): void
    {
        $input = (new EstimateInputBuilder())->build();
        $reflection = new \ReflectionClass($input);
        $propertyNames = array_map(
            static fn(\ReflectionProperty $p): string => $p->getName(),
            $reflection->getProperties()
        );

        foreach (['email', 'phone', 'name', 'firstname', 'lastname', 'company'] as $piiField) {
            self::assertNotContains($piiField, $propertyNames, sprintf('PII field "%s" must not be in ProjectEstimateInput.', $piiField));
        }
    }
}
