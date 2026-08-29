<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Support;

use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\CurrentSituation;
use App\Estimator\Domain\ProjectEstimateInput;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectObjective;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;

/**
 * Test fixture builder — ces valeurs sont des données de test et ne représentent pas les tarifs Devzair.
 */
final class EstimateInputBuilder
{
    private ProjectType $projectType        = ProjectType::VitrineSite;
    private array $objectives               = [ProjectObjective::PresentBusiness];
    private CurrentSituation $situation     = CurrentSituation::None;
    private ProjectScale $scale             = ProjectScale::Small;
    private array $features                 = [];
    private array $contentNeeds             = [];
    private array $visibilityNeeds          = [];
    private array $careNeeds                = [];

    public function withProjectType(ProjectType $type): self
    {
        $clone = clone $this;
        $clone->projectType = $type;

        return $clone;
    }

    /** @param list<ProjectObjective> $objectives */
    public function withObjectives(array $objectives): self
    {
        $clone = clone $this;
        $clone->objectives = $objectives;

        return $clone;
    }

    public function withCurrentSituation(CurrentSituation $situation): self
    {
        $clone = clone $this;
        $clone->situation = $situation;

        return $clone;
    }

    public function withScale(ProjectScale $scale): self
    {
        $clone = clone $this;
        $clone->scale = $scale;

        return $clone;
    }

    /** @param list<ProjectFeature> $features */
    public function withFeatures(array $features): self
    {
        $clone = clone $this;
        $clone->features = $features;

        return $clone;
    }

    /** @param list<ContentNeed> $needs */
    public function withContentNeeds(array $needs): self
    {
        $clone = clone $this;
        $clone->contentNeeds = $needs;

        return $clone;
    }

    /** @param list<VisibilityNeed> $needs */
    public function withVisibilityNeeds(array $needs): self
    {
        $clone = clone $this;
        $clone->visibilityNeeds = $needs;

        return $clone;
    }

    /** @param list<CareNeed> $needs */
    public function withCareNeeds(array $needs): self
    {
        $clone = clone $this;
        $clone->careNeeds = $needs;

        return $clone;
    }

    public function build(): ProjectEstimateInput
    {
        return new ProjectEstimateInput(
            projectType:      $this->projectType,
            objectives:       $this->objectives,
            currentSituation: $this->situation,
            scale:            $this->scale,
            features:         $this->features,
            contentNeeds:     $this->contentNeeds,
            visibilityNeeds:  $this->visibilityNeeds,
            careNeeds:        $this->careNeeds,
        );
    }
}
