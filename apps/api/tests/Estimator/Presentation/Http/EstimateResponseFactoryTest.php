<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Presentation\Http;

use App\Estimator\Application\Pricing\DevzairPricingCatalogV1;
use App\Estimator\Application\Pricing\ProjectEstimationEngine;
use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Presentation\Http\EstimateResponseFactory;
use App\Tests\Estimator\Support\EstimateInputBuilder;
use PHPUnit\Framework\TestCase;

final class EstimateResponseFactoryTest extends TestCase
{
    private ProjectEstimationEngine $engine;
    private EstimateResponseFactory $factory;

    protected function setUp(): void
    {
        $this->engine  = new ProjectEstimationEngine(new DevzairPricingCatalogV1());
        $this->factory = new EstimateResponseFactory();
    }

    public function testOutcomeIsEstimatedForKnownType(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())->withProjectType(ProjectType::VitrineSite)->build()
        );
        $payload = $this->factory->build($result, 'test-id');

        self::assertSame('estimated', $payload['outcome']);
    }

    public function testOutcomeIsHumanScopingRequiredForUnknownFallback(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([])
                ->build()
        );
        $payload = $this->factory->build($result, 'test-id');

        self::assertSame('human_scoping_required', $payload['outcome']);
    }

    public function testStatusIsOk(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())->withProjectType(ProjectType::VitrineSite)->build()
        );
        $payload = $this->factory->build($result, 'req-123');

        self::assertSame('ok', $payload['status']);
        self::assertSame('req-123', $payload['request_id']);
    }

    public function testEstimateContainsNoFloat(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Ecommerce)
                ->withScale(ProjectScale::Medium)
                ->build()
        );
        $payload = $this->factory->build($result, 'test-id');

        self::assertIsInt($payload['estimate']['minimum']);
        self::assertIsInt($payload['estimate']['maximum']);

        foreach ($payload['one_off_items'] as $item) {
            self::assertIsInt($item['minimum']);
            self::assertIsInt($item['maximum']);
        }
    }

    public function testRecurringItemsHavePeriodMonth(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withCareNeeds([CareNeed::Maintenance])
                ->build()
        );
        $payload = $this->factory->build($result, 'test-id');

        self::assertNotEmpty($payload['recurring_items']);
        foreach ($payload['recurring_items'] as $item) {
            self::assertSame('month', $item['period']);
            self::assertIsInt($item['minimum']);
            self::assertIsInt($item['maximum']);
            self::assertSame('EUR', $item['currency']);
        }
    }

    public function testOneOffItemsHaveNoPeriod(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())->withProjectType(ProjectType::VitrineSite)->build()
        );
        $payload = $this->factory->build($result, 'test-id');

        foreach ($payload['one_off_items'] as $item) {
            self::assertArrayNotHasKey('period', $item);
            self::assertSame('EUR', $item['currency']);
        }
    }

    public function testAssumptionsAreCodes(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Refonte)
                ->withScale(ProjectScale::Small)
                ->build()
        );
        $payload = $this->factory->build($result, 'test-id');

        self::assertContains('redesign_existing_system_to_audit', $payload['assumptions']);

        foreach ($payload['assumptions'] as $assumption) {
            self::assertIsString($assumption);
            self::assertStringNotContainsString(' ', $assumption, 'Assumption doit être un code snake_case, pas un label UI.');
        }
    }

    public function testPricingVersionIs2026v1(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())->build()
        );
        $payload = $this->factory->build($result, 'test-id');

        self::assertSame('2026-v1', $payload['pricing_version']);
    }

    public function testRecommendedProjectTypeIsStringValue(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Ecommerce)
                ->build()
        );
        $payload = $this->factory->build($result, 'test-id');

        self::assertSame('ecommerce', $payload['recommended_project_type']);
    }

    public function testCurrencyIsEurEverywhere(): void
    {
        $result  = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withCareNeeds([CareNeed::Maintenance])
                ->build()
        );
        $payload = $this->factory->build($result, 'test-id');

        self::assertSame('EUR', $payload['estimate']['currency']);
        foreach ($payload['one_off_items'] as $item) {
            self::assertSame('EUR', $item['currency']);
        }
        foreach ($payload['recurring_items'] as $item) {
            self::assertSame('EUR', $item['currency']);
        }
    }
}
