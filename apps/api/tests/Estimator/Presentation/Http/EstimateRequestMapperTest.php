<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Presentation\Http;

use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\CurrentSituation;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectObjective;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;
use App\Estimator\Presentation\Http\EstimateRequest;
use App\Estimator\Presentation\Http\EstimateRequestMapper;
use PHPUnit\Framework\TestCase;

final class EstimateRequestMapperTest extends TestCase
{
    private EstimateRequestMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new EstimateRequestMapper();
    }

    public function testMapsProjectType(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'vitrinesite';
        $dto->current_situation = 'none';
        $dto->scale        = 'small';

        $input = $this->mapper->map($dto);

        self::assertSame(ProjectType::VitrineSite, $input->projectType);
    }

    public function testMapsAllKnownProjectTypes(): void
    {
        $cases = [
            'vitrinesite' => ProjectType::VitrineSite,
            'ecommerce'   => ProjectType::Ecommerce,
            'businessapp' => ProjectType::BusinessApp,
            'refonte'     => ProjectType::Refonte,
            'other'       => ProjectType::Other,
            'unknown'     => ProjectType::Unknown,
        ];

        foreach ($cases as $value => $expected) {
            $dto               = new EstimateRequest();
            $dto->project_type = $value;
            $dto->current_situation = 'none';
            $dto->scale        = 'small';

            self::assertSame($expected, $this->mapper->map($dto)->projectType, "Failed for: $value");
        }
    }

    public function testMapsObjectives(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'unknown';
        $dto->current_situation = 'none';
        $dto->scale        = 'small';
        $dto->objectives   = ['present_business', 'generate_leads'];

        $input = $this->mapper->map($dto);

        self::assertCount(2, $input->objectives);
        self::assertSame(ProjectObjective::PresentBusiness, $input->objectives[0]);
        self::assertSame(ProjectObjective::GenerateLeads, $input->objectives[1]);
    }

    public function testMapsCurrentSituation(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'vitrinesite';
        $dto->current_situation = 'has_website';
        $dto->scale        = 'small';

        $input = $this->mapper->map($dto);

        self::assertSame(CurrentSituation::HasWebsite, $input->currentSituation);
    }

    public function testMapsScale(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'vitrinesite';
        $dto->current_situation = 'none';
        $dto->scale        = 'medium';

        $input = $this->mapper->map($dto);

        self::assertSame(ProjectScale::Medium, $input->scale);
    }

    public function testMapsFeatures(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'ecommerce';
        $dto->current_situation = 'none';
        $dto->scale        = 'small';
        $dto->features     = ['payment', 'shipping'];

        $input = $this->mapper->map($dto);

        self::assertCount(2, $input->features);
        self::assertSame(ProjectFeature::Payment, $input->features[0]);
        self::assertSame(ProjectFeature::Shipping, $input->features[1]);
    }

    public function testMapsContentNeeds(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'vitrinesite';
        $dto->current_situation = 'none';
        $dto->scale        = 'small';
        $dto->content_needs = ['visual_identity_needed', 'content_to_write'];

        $input = $this->mapper->map($dto);

        self::assertCount(2, $input->contentNeeds);
        self::assertSame(ContentNeed::VisualIdentityNeeded, $input->contentNeeds[0]);
        self::assertSame(ContentNeed::ContentToWrite, $input->contentNeeds[1]);
    }

    public function testMapsVisibilityNeeds(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'vitrinesite';
        $dto->current_situation = 'none';
        $dto->scale        = 'small';
        $dto->visibility_needs = ['seo_advanced'];

        $input = $this->mapper->map($dto);

        self::assertCount(1, $input->visibilityNeeds);
        self::assertSame(VisibilityNeed::SeoAdvanced, $input->visibilityNeeds[0]);
    }

    public function testMapsCareNeeds(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'vitrinesite';
        $dto->current_situation = 'none';
        $dto->scale        = 'small';
        $dto->care_needs   = ['maintenance', 'continuous_seo'];

        $input = $this->mapper->map($dto);

        self::assertCount(2, $input->careNeeds);
        self::assertSame(CareNeed::Maintenance, $input->careNeeds[0]);
        self::assertSame(CareNeed::ContinuousSeo, $input->careNeeds[1]);
    }

    public function testEmptyArraysAreAllowed(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'vitrinesite';
        $dto->current_situation = 'none';
        $dto->scale        = 'small';

        $input = $this->mapper->map($dto);

        self::assertSame([], $input->objectives);
        self::assertSame([], $input->features);
        self::assertSame([], $input->contentNeeds);
        self::assertSame([], $input->visibilityNeeds);
        self::assertSame([], $input->careNeeds);
    }

    public function testDomainDeduplicatesArrays(): void
    {
        $dto               = new EstimateRequest();
        $dto->project_type = 'vitrinesite';
        $dto->current_situation = 'none';
        $dto->scale        = 'small';
        $dto->features     = ['payment', 'payment'];

        $input = $this->mapper->map($dto);

        self::assertCount(1, $input->features);
    }
}
