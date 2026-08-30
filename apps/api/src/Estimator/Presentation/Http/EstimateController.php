<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Http;

use App\Contact\Security\OriginAllowlist;
use App\Estimator\Application\Pricing\ProjectEstimationEngine;
use App\Estimator\Infrastructure\Security\EstimateRateLimiter;
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
 * POST /api/estimate — calcule une estimation indicative à partir du questionnaire.
 *
 * Pipeline :
 * 1. génère un Request-Id (UUID v7) pour la corrélation logs/réponse ;
 * 2. contrôle CSRF stateless (Origin allowlist — même policy que /api/contact) ;
 * 3. rejette les payloads > 10 KB ;
 * 4. rate limit par IP — bucket estimate_ip dédié (30/min par défaut) ;
 * 5. désérialise le JSON → EstimateRequest ;
 * 6. valide via Symfony Validator → 400 validation_error si violation ;
 * 7. mappe vers ProjectEstimateInput (domaine) ;
 * 8. délègue à ProjectEstimationEngine (aucune logique tarifaire ici) ;
 * 9. construit la réponse JSON (outcome, estimate, line items, assumptions).
 *
 * Garanties :
 * - aucun PII collecté ni loggué ;
 * - payload jamais loggué ;
 * - aucune persistence ;
 * - Cache-Control: no-store sur toutes les réponses.
 */
#[AsController]
final class EstimateController
{
    private const MAX_PAYLOAD_BYTES = 10_000;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EstimateRateLimiter $rateLimiter,
        private readonly OriginAllowlist $originAllowlist,
        private readonly EstimateRequestMapper $mapper,
        private readonly ProjectEstimationEngine $engine,
        private readonly EstimateResponseFactory $responseFactory,
        private readonly LoggerInterface $estimatorLogger,
    ) {}

    public function __invoke(Request $request): Response
    {
        $requestId = Uuid::v7()->toRfc4122();

        // 1. Origin (CSRF stateless — même politique que /api/contact)
        if (!$this->originAllowlist->isAllowed($request)) {
            $this->estimatorLogger->info('estimator.origin_rejected', [
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
            $this->estimatorLogger->info('estimator.payload_too_large', [
                'request_id' => $requestId,
                'size'       => \strlen($rawPayload),
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                ['status' => 'error', 'code' => 'payload_too_large'],
            );
        }

        // 3. Rate limit (bucket estimate_ip, indépendant du bucket contact)
        $rateLimit = $this->rateLimiter->consume($request);

        if (!$rateLimit->isAccepted()) {
            $retryAfter = max(1, $rateLimit->getRetryAfter()->getTimestamp() - time());

            $this->estimatorLogger->info('estimator.rate_limited', [
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
                EstimateRequest::class,
                'json',
            );
        } catch (NotEncodableValueException | NotNormalizableValueException) {
            $this->estimatorLogger->info('estimator.invalid_json', [
                'request_id' => $requestId,
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_BAD_REQUEST,
                ['status' => 'error', 'code' => 'invalid_json'],
            );
        }

        // 5. Validation Symfony Validator
        $violations = $this->validator->validate($dto);

        if (\count($violations) > 0) {
            $this->estimatorLogger->info('estimator.validation_failed', [
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

        // 6. Mapping Presentation → Domaine
        $input = $this->mapper->map($dto);

        // 7. Estimation (moteur autoritaire — aucune logique tarifaire ici)
        $result = $this->engine->estimate($input);

        // 8. Construction de la réponse
        $payload = $this->responseFactory->build($result, $requestId);

        $this->estimatorLogger->info('estimator.estimate_generated', [
            'request_id'      => $requestId,
            'pricing_version' => $result->pricingVersion->value(),
            'outcome'         => $payload['outcome'],
        ]);

        return $this->respond($requestId, Response::HTTP_OK, $payload);
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
