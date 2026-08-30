<?php

declare(strict_types=1);

namespace App\Estimator\Application\Lead;

use App\Estimator\Domain\Lead\EstimatorLead;
use App\Estimator\Domain\Lead\EstimatorLeadRepositoryInterface;
use App\Estimator\Application\Pricing\ProjectEstimationEngine;
use App\Estimator\Presentation\Http\EstimateResponseFactory;
use App\Estimator\Presentation\Http\Lead\EstimateLeadRequest;
use App\Estimator\Presentation\Http\Lead\EstimateLeadRequestMapper;
use Symfony\Component\Uid\Uuid;

/**
 * Cas d'usage : soumettre un lead qualifié de l'estimateur.
 *
 * Garanties :
 * - Symfony recalcule l'estimation — les prix du navigateur ne sont JAMAIS lus ;
 * - `additional_feature_note` est stockée mais jamais transmise au moteur de prix ;
 * - pour outcome = human_scoping_required, les montants en DB sont NULL ;
 * - l'email de notification est best-effort (échec = warning, lead déjà persisté).
 */
final class SubmitEstimatorLead
{
    public function __construct(
        private readonly EstimateLeadRequestMapper  $mapper,
        private readonly ProjectEstimationEngine    $engine,
        private readonly EstimateResponseFactory    $responseFactory,
        private readonly EstimatorLeadRepositoryInterface $repository,
        private readonly EstimatorLeadNotifier      $notifier,
    ) {}

    public function submit(EstimateLeadRequest $dto, string $requestId): EstimatorLead
    {
        // 1. Recalcul autoritaire de l'estimation (jamais les prix du client)
        $input  = $this->mapper->map($dto);
        $result = $this->engine->estimate($input);

        // 2. Construction des snapshots (sans PII pour le questionnaire)
        $questionnaireSnapshot = $this->mapper->toQuestionnaireSnapshot($dto);
        $estimateSnapshot      = $this->responseFactory->build($result, $requestId);

        // 3. Extraction outcome + montants
        $outcome = $estimateSnapshot['outcome'];

        $minorMin = ($outcome === 'human_scoping_required')
            ? null
            : $estimateSnapshot['estimate']['minimum'];

        $minorMax = ($outcome === 'human_scoping_required')
            ? null
            : $estimateSnapshot['estimate']['maximum'];

        // 4. Création de l'entité
        $lead = EstimatorLead::create(
            id:                    Uuid::v7(),
            name:                  trim($dto->name),
            email:                 trim($dto->email),
            phone:                 isset($dto->phone) ? trim($dto->phone) : null,
            company:               isset($dto->company) ? trim($dto->company) : null,
            additionalFeatureNote: isset($dto->additional_feature_note) ? trim($dto->additional_feature_note) : null,
            projectType:           $estimateSnapshot['recommended_project_type'],
            outcome:               $outcome,
            estimateMinimumMinor:  $minorMin,
            estimateMaximumMinor:  $minorMax,
            pricingVersion:        $estimateSnapshot['pricing_version'],
            questionnaireSnapshot: $questionnaireSnapshot,
            estimateSnapshot:      $estimateSnapshot,
            requestId:             $requestId,
            createdAt:             new \DateTimeImmutable(),
        );

        // 5. Persistance
        $this->repository->save($lead);

        // 6. Notification best-effort (échec n'empêche pas la réponse 201)
        $this->notifier->notify($lead);

        return $lead;
    }
}
