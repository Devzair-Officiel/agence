<?php

declare(strict_types=1);

namespace App\Contact\Controller;

use App\Contact\Dto\ContactRequest;
use App\Contact\Security\ContactRateLimiter;
use App\Contact\Security\OriginAllowlist;
use App\Contact\Service\ContactSubmissionResult;
use App\Contact\Service\SubmitContactMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * POST /api/contact — reçoit une soumission du formulaire de contact.
 *
 * Pipeline :
 * 1. génère un Request-Id (UUID v7) pour la corrélation logs/réponse ;
 * 2. contrôle CSRF Option B (Origin allowlist, voir ADR-007) ;
 * 3. rejette payloads > 10 KB ;
 * 4. rate limit par IP → 429 + Retry-After ;
 * 5. deserialize + honeypot → 202 silencieux si `website` non vide ;
 * 6. délègue à SubmitContactMessage (validation, Turnstile, envoi) ;
 * 7. formatte la réponse JSON (200 succès, 400 validation, 403 Turnstile).
 *
 * Aucun PII (message, email, téléphone, token, secret) n'est loggué.
 */
#[AsController]
final class ContactSubmissionController
{
    private const MAX_PAYLOAD_BYTES = 10_000;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly SubmitContactMessage $submitContactMessage,
        private readonly ContactRateLimiter $rateLimiter,
        private readonly OriginAllowlist $originAllowlist,
        private readonly LoggerInterface $contactLogger,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $requestId = Uuid::v7()->toRfc4122();

        if (!$this->originAllowlist->isAllowed($request)) {
            $this->contactLogger->info('contact.origin_rejected', [
                'request_id' => $requestId,
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_FORBIDDEN,
                ['status' => 'forbidden', 'code' => 'origin_not_allowed'],
            );
        }

        $rawPayload = $request->getContent();

        if (\strlen($rawPayload) > self::MAX_PAYLOAD_BYTES) {
            $this->contactLogger->info('contact.payload_too_large', [
                'request_id' => $requestId,
                'size' => \strlen($rawPayload),
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                ['status' => 'error', 'code' => 'payload_too_large'],
            );
        }

        $rateLimit = $this->rateLimiter->consume($request);

        if (!$rateLimit->isAccepted()) {
            $retryAfter = max(1, $rateLimit->getRetryAfter()->getTimestamp() - time());

            $this->contactLogger->info('contact.rate_limited', [
                'request_id' => $requestId,
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_TOO_MANY_REQUESTS,
                ['status' => 'error', 'code' => 'rate_limited'],
                ['Retry-After' => (string) $retryAfter],
            );
        }

        try {
            $dto = $this->serializer->deserialize(
                $rawPayload === '' ? '{}' : $rawPayload,
                ContactRequest::class,
                'json',
            );
        } catch (NotEncodableValueException) {
            $this->contactLogger->info('contact.invalid_json', [
                'request_id' => $requestId,
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_BAD_REQUEST,
                ['status' => 'error', 'code' => 'invalid_json'],
            );
        }

        // Honeypot : bot rempli le champ → on répond 202 générique et on
        // ne dépense aucune ressource (pas de validation, pas d'email).
        if (trim($dto->website) !== '') {
            $this->contactLogger->info('contact.honeypot_triggered', [
                'request_id' => $requestId,
            ]);

            return $this->respond(
                $requestId,
                Response::HTTP_ACCEPTED,
                ['status' => 'accepted'],
            );
        }

        $result = ($this->submitContactMessage)($dto, $request, $requestId);

        return match ($result->status) {
            ContactSubmissionResult::STATUS_ACCEPTED => $this->respond(
                $requestId,
                Response::HTTP_OK,
                ['status' => 'accepted'],
            ),
            ContactSubmissionResult::STATUS_VALIDATION_ERROR => $this->respond(
                $requestId,
                Response::HTTP_BAD_REQUEST,
                [
                    'status' => 'error',
                    'code' => 'validation_failed',
                    'errors' => $result->errors,
                ],
            ),
            ContactSubmissionResult::STATUS_TURNSTILE_REJECTED => $this->respond(
                $requestId,
                Response::HTTP_FORBIDDEN,
                ['status' => 'error', 'code' => 'turnstile_rejected'],
            ),
        };
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
