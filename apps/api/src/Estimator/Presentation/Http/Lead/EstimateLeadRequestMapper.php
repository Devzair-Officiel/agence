<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Http\Lead;

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
 * Convertit les champs questionnaire d'un EstimateLeadRequest validé
 * en ProjectEstimateInput (domaine).
 *
 * Les champs contact (name, email, phone, company) ne sont pas transmis
 * au domaine estimateur.
 */
final class EstimateLeadRequestMapper
{
    public function map(EstimateLeadRequest $dto): ProjectEstimateInput
    {
        return new ProjectEstimateInput(
            projectType:      ProjectType::fromString($dto->project_type),
            objectives:       array_map(
                static fn(string $v): ProjectObjective => ProjectObjective::fromString($v),
                $dto->objectives,
            ),
            currentSituation: CurrentSituation::fromString($dto->current_situation),
            scale:            ProjectScale::fromString($dto->scale),
            features:         array_map(
                static fn(string $v): ProjectFeature => ProjectFeature::fromString($v),
                $dto->features,
            ),
            contentNeeds:     array_map(
                static fn(string $v): ContentNeed => ContentNeed::fromString($v),
                $dto->content_needs,
            ),
            visibilityNeeds:  array_map(
                static fn(string $v): VisibilityNeed => VisibilityNeed::fromString($v),
                $dto->visibility_needs,
            ),
            careNeeds:        array_map(
                static fn(string $v): CareNeed => CareNeed::fromString($v),
                $dto->care_needs,
            ),
        );
    }

    /**
     * Construit le snapshot questionnaire persisté en JSONB.
     * Aucun PII : ne contient pas name, email, phone, company,
     * additional_feature_note.
     *
     * @return array<string, mixed>
     */
    public function toQuestionnaireSnapshot(EstimateLeadRequest $dto): array
    {
        return [
            'project_type'      => $dto->project_type,
            'objectives'        => $dto->objectives,
            'current_situation' => $dto->current_situation,
            'scale'             => $dto->scale,
            'features'          => $dto->features,
            'content_needs'     => $dto->content_needs,
            'visibility_needs'  => $dto->visibility_needs,
            'care_needs'        => $dto->care_needs,
        ];
    }
}
