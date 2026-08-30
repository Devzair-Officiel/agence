<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Http\Partnership;

use App\Contact\Security\OriginAllowlist;
use App\Estimator\Application\Partnership\SubmitEstimatorPartnership;
use App\Estimator\Infrastructure\Security\EstimatePartnershipRateLimiter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * POST /api/estimate/partnership — qualifie une proposition de partenariat.
 *
 * Pipeline identique à /api/estimate/lead :
 * 1. Request-Id UUID v7 pour corrélation ;
 * 2. Origin allowlist (CSRF stateless) ;
 * 3. Payload ≤ 14 KB (questionnaire + textes libres 500 car.) ;
 * 4. Rate limit bucket estimate_partnership_ip ;
 * 5. Désérialisation JSON → EstimatorPartnershipRequest ;
 * 6. Validation Symfony Validator → 400 si violation ;
 * 7. Honeypot `website` → 202 silencieux si non vide ;
 * 8. Délègue à SubmitEstimatorPartnership (recalcul + persistance) ;
 * 9. Répond 201 Created.
 *
 * Garanties :
 * - aucun PII dans les logs ;
 * - status = pending_review à la création (jamais acceptation automatique) ;
 * - aucun prix frontend accepté ;
 * - Cache-Control: no-store sur toutes les réponses.
 */
#[AsController]
final class EstimatorPartnershipController
{
    private const MAX_PAYLOAD_BYTES = 14_000;

    public function __construct(
        private readonly SerializerInterface             $serializer,
        private readonly ValidatorInterface              $validator,
        private readonly EstimatePartnershipRateLimiter  $rateLimiter,
        private readonly OriginAllowlist                 $originAllowlist,
        private readonly SubmitEstimatorPartnership      $submitPartnership,
        private readonly LoggerInterface                 $estimatorPartnershipLogger,
    ) {}

    public function __invoke(Request $request): Response
    {
        $requestId = Uuid::v7()->toRfc4122();

        // 1. Origin
        if (!$this->originAllowlist->isAllowed($request)) {
            $this->estimatorPartnershipLogger->info('estimator_partnership.origin_rejected', [
                'request_id' => $requestId,
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_FORBIDDEN,
                ['status' => 'forbidden', 'code' => 'origin_not_allowed'],
            );
        }

        // 2. Taille du payload
        $rawPayload = $request->getContent();

        if (\strlen($rawPayload) > self::MAX_PAYLOAD_BYTES) {
            $this->estimatorPartnershipLogger->info('estimator_partnership.payload_too_large', [
                'request_id' => $requestId,
                'size'       => \strlen($rawPayload),
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                ['status' => 'error', 'code' => 'payload_too_large'],
            );
        }

        // 3. Rate limit
        $rateLimit = $this->rateLimiter->consume($request);

        if (!$rateLimit->isAccepted()) {
            $retryAfter = max(1, $rateLimit->getRetryAfter()->getTimestamp() - time());

            $this->estimatorPartnershipLogger->info('estimator_partnership.rate_limited', [
                'request_id' => $requestId,
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_TOO_MANY_REQUESTS,
                ['status' => 'error', 'code' => 'rate_limited'],
                ['Retry-After' => (string) $retryAfter],
            );
        }

        // 4. Désérialisation JSON
        try {
            $dto = $this->serializer->deserialize(
                $rawPayload === '' ? '{}' : $rawPayload,
                EstimatorPartnershipRequest::class,
                'json',
            );
        } catch (NotEncodableValueException | NotNormalizableValueException) {
            $this->estimatorPartnershipLogger->info('estimator_partnership.invalid_json', [
                'request_id' => $requestId,
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_BAD_REQUEST,
                ['status' => 'error', 'code' => 'invalid_json'],
            );
        }

        // 5. Validation
        $violations = $this->validator->validate($dto);

        if (\count($violations) > 0) {
            $this->estimatorPartnershipLogger->info('estimator_partnership.validation_failed', [
                'request_id' => $requestId,
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_BAD_REQUEST,
                [
                    'status' => 'error',
                    'code'   => 'validation_error',
                    'errors' => $this->formatViolations($violations),
                ],
            );
        }

        // 6. Honeypot — 202 silencieux
        if (trim($dto->website) !== '') {
            return $this->respond(
                $requestId,
                Response::HTTP_ACCEPTED,
                ['status' => 'ok', 'request_id' => $requestId],
            );
        }

        // 7. Persistance (status = pending_review, jamais acceptation automatique)
        $proposal = $this->submitPartnership->submit($dto, $requestId);

        $this->estimatorPartnershipLogger->info('estimator_partnership.created', [
            'request_id'       => $requestId,
            'proposal_id'      => $proposal->id()->toRfc4122(),
            'outcome'          => $proposal->projectType(),
            'partnership_type' => $proposal->partnershipType(),
            'project_stage'    => $proposal->projectStage(),
        ]);

        return $this->respond(
            $requestId,
            Response::HTTP_CREATED,
            [
                'status'      => 'submitted',
                'proposal_id' => $proposal->id()->toRfc4122(),
            ],
        );
    }

    /**
     * @return array<string, list<string>>
     */
    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $property          = $violation->getPropertyPath();
            $errors[$property][] = (string) $violation->getMessage();
        }

        return $errors;
    }

    /**
     * @param array<string, mixed>  $payload
     * @param array<string, string> $extraHeaders
     */
    private function respond(string $requestId, int $status, array $payload, array $extraHeaders = []): JsonResponse
    {
        $payload['request_id'] = $requestId;

        $response = new JsonResponse($payload, $status);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('Cache-Control', 'no-store');

        foreach ($extraHeaders as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
