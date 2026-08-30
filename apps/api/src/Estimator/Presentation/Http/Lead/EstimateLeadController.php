<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Http\Lead;

use App\Contact\Security\OriginAllowlist;
use App\Estimator\Application\Lead\SubmitEstimatorLead;
use App\Estimator\Infrastructure\Security\EstimateLeadRateLimiter;
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
 * POST /api/estimate/lead — persiste un lead qualifié de l'estimateur.
 *
 * Pipeline :
 * 1. Request-Id (UUID v7) pour corrélation logs/réponse ;
 * 2. Origin allowlist (CSRF stateless — même policy que /api/estimate) ;
 * 3. Payload ≤ 12 KB (contact + questionnaire) ;
 * 4. Rate limit bucket estimate_lead_ip (10/min — plus strict que estimate_ip) ;
 * 5. Désérialisation JSON → EstimateLeadRequest ;
 * 6. Validation Symfony Validator → 400 si violation ;
 * 7. Honeypot `website` — 202 silencieux si non vide (anti-bot) ;
 * 8. Délègue à SubmitEstimatorLead (recalcul estimation + persistance + notif) ;
 * 9. Répond 201 Created.
 *
 * Garanties :
 * - aucun PII dans les logs (ni name, ni email, ni phone, ni company, ni note) ;
 * - payload jamais loggué ;
 * - Cache-Control: no-store sur toutes les réponses.
 */
#[AsController]
final class EstimateLeadController
{
    private const MAX_PAYLOAD_BYTES = 12_000;

    public function __construct(
        private readonly SerializerInterface      $serializer,
        private readonly ValidatorInterface       $validator,
        private readonly EstimateLeadRateLimiter  $rateLimiter,
        private readonly OriginAllowlist          $originAllowlist,
        private readonly SubmitEstimatorLead      $submitLead,
        private readonly LoggerInterface          $estimatorLeadLogger,
    ) {}

    public function __invoke(Request $request): Response
    {
        $requestId = Uuid::v7()->toRfc4122();

        // 1. Origin
        if (!$this->originAllowlist->isAllowed($request)) {
            $this->estimatorLeadLogger->info('estimator_lead.origin_rejected', [
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
            $this->estimatorLeadLogger->info('estimator_lead.payload_too_large', [
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

            $this->estimatorLeadLogger->info('estimator_lead.rate_limited', [
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
                EstimateLeadRequest::class,
                'json',
            );
        } catch (NotEncodableValueException | NotNormalizableValueException) {
            $this->estimatorLeadLogger->info('estimator_lead.invalid_json', [
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
            $this->estimatorLeadLogger->info('estimator_lead.validation_failed', [
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

        // 6. Honeypot — réponse silencieuse 202 (indiscernable du succès)
        if (trim($dto->website) !== '') {
            return $this->respond(
                $requestId,
                Response::HTTP_ACCEPTED,
                ['status' => 'ok', 'request_id' => $requestId],
            );
        }

        // 7. Persistance + notification
        $lead = $this->submitLead->submit($dto, $requestId);

        $this->estimatorLeadLogger->info('estimator_lead.created', [
            'request_id'   => $requestId,
            'outcome'      => $lead->outcome(),
            'project_type' => $lead->projectType(),
        ]);

        return $this->respond(
            $requestId,
            Response::HTTP_CREATED,
            ['status' => 'ok'],
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
