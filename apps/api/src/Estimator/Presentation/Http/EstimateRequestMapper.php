<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Http;

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
 * Convertit un EstimateRequest (DTO HTTP validé) en ProjectEstimateInput (domaine).
 *
 * Pré-condition : le DTO a déjà passé la validation Symfony Validator.
 * Les appels `fromString()` ne doivent pas lever d'exception.
 */
final class EstimateRequestMapper
{
    public function map(EstimateRequest $dto): ProjectEstimateInput
    {
        return new ProjectEstimateInput(
            projectType:     ProjectType::fromString($dto->project_type),
            objectives:      array_map(
                static fn(string $v): ProjectObjective => ProjectObjective::fromString($v),
                $dto->objectives,
            ),
            currentSituation: CurrentSituation::fromString($dto->current_situation),
            scale:           ProjectScale::fromString($dto->scale),
            features:        array_map(
                static fn(string $v): ProjectFeature => ProjectFeature::fromString($v),
                $dto->features,
            ),
            contentNeeds:    array_map(
                static fn(string $v): ContentNeed => ContentNeed::fromString($v),
                $dto->content_needs,
            ),
            visibilityNeeds: array_map(
                static fn(string $v): VisibilityNeed => VisibilityNeed::fromString($v),
                $dto->visibility_needs,
            ),
            careNeeds:       array_map(
                static fn(string $v): CareNeed => CareNeed::fromString($v),
                $dto->care_needs,
            ),
        );
    }
}
