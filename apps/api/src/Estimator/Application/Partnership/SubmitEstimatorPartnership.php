<?php

declare(strict_types=1);

namespace App\Estimator\Application\Partnership;

use App\Estimator\Application\Pricing\ProjectEstimationEngine;
use App\Estimator\Domain\Partnership\EstimatorPartnershipProposal;
use App\Estimator\Domain\Partnership\EstimatorPartnershipRepositoryInterface;
use App\Estimator\Presentation\Http\EstimateResponseFactory;
use App\Estimator\Presentation\Http\Partnership\EstimatorPartnershipRequest;
use App\Estimator\Presentation\Http\Partnership\EstimatorPartnershipRequestMapper;
use Symfony\Component\Uid\Uuid;

/**
 * Cas d'usage : soumettre une proposition de partenariat.
 *
 * Garanties :
 * - Symfony recalcule TOUJOURS l'estimation — les prix du navigateur ne sont
 *   JAMAIS acceptés ;
 * - statut initial = pending_review (aucune acceptation automatique) ;
 * - aucun champ `percentage`, `equity`, `commission` n'est calculé ;
 * - pour outcome = human_scoping_required, les montants en DB sont NULL ;
 * - aucune notification email en V1 (dette opérationnelle documentée).
 */
final class SubmitEstimatorPartnership
{
    public function __construct(
        private readonly EstimatorPartnershipRequestMapper   $mapper,
        private readonly ProjectEstimationEngine             $engine,
        private readonly EstimateResponseFactory             $responseFactory,
        private readonly EstimatorPartnershipRepositoryInterface $repository,
    ) {}

    public function submit(
        EstimatorPartnershipRequest $dto,
        string $requestId,
    ): EstimatorPartnershipProposal {
        // 1. Recalcul autoritaire (jamais les prix du client)
        $input  = $this->mapper->map($dto);
        $result = $this->engine->estimate($input);

        // 2. Snapshots
        $questionnaireSnapshot = $this->mapper->toQuestionnaireSnapshot($dto);
        $estimateSnapshot      = $this->responseFactory->build($result, $requestId);

        // 3. Extraction outcome + montants
        $outcome = $estimateSnapshot['outcome'];

        if ($outcome === 'human_scoping_required') {
            $minorMin = null;
            $minorMax = null;
            $currency = null;
        } else {
            $minorMin = $estimateSnapshot['estimate']['minimum'];
            $minorMax = $estimateSnapshot['estimate']['maximum'];
            $currency = $estimateSnapshot['estimate']['currency'];
        }

        // 4. Création de l'entité (status = pending_review, jamais auto-accepté)
        $proposal = EstimatorPartnershipProposal::create(
            id:                   Uuid::v7(),
            leadRequestId:        isset($dto->lead_request_id) ? trim((string) $dto->lead_request_id) : null,
            name:                 trim($dto->name),
            email:                trim($dto->email),
            phone:                isset($dto->phone) ? trim($dto->phone) : null,
            company:              isset($dto->company) ? trim($dto->company) : null,
            projectType:          $estimateSnapshot['recommended_project_type'],
            pricingVersion:       $estimateSnapshot['pricing_version'],
            estimateMinimumMinor: $minorMin,
            estimateMaximumMinor: $minorMax,
            currency:             $currency,
            partnershipType:      $dto->partnership_type,
            projectStage:         $dto->project_stage,
            generatesRevenue:     isset($dto->generates_revenue) ? trim((string) $dto->generates_revenue) : null,
            proposalText:         trim($dto->proposal_text),
            relevanceText:        isset($dto->relevance_text) ? trim($dto->relevance_text) : null,
            questionnaireSnapshot: $questionnaireSnapshot,
            estimateSnapshot:      $estimateSnapshot,
            requestId:             $requestId,
            createdAt:             new \DateTimeImmutable(),
        );

        // 5. Persistance
        $this->repository->save($proposal);

        // NOTE: Pas de notification email en V1. Dette opérationnelle :
        // ajouter un EstimatorPartnershipNotifier (pattern EST-6) quand
        // une surveillance admin régulière sera formalisée.

        return $proposal;
    }
}
