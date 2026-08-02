<?php

declare(strict_types=1);

namespace App\Contact\Service;

use App\Contact\Dto\ContactRequest;
use App\Contact\Security\TurnstileVerifierInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Cas d'usage principal : valider la requête, vérifier le token Turnstile,
 * puis déléguer l'envoi à ContactMessageSenderInterface.
 *
 * Ne connaît ni HTTP ni Mailer : reçoit un ContactRequest déjà déserialisé
 * et retourne un ContactSubmissionResult. Le rate limit et le honeypot sont
 * traités *avant* ce service par le contrôleur, pour éviter tout log/CPU sur
 * du trafic manifestement abusif.
 */
final class SubmitContactMessage
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly TurnstileVerifierInterface $turnstileVerifier,
        private readonly ContactMessageSenderInterface $sender,
        private readonly LoggerInterface $contactLogger,
    ) {
    }

    public function __invoke(ContactRequest $request, Request $httpRequest, string $requestId): ContactSubmissionResult
    {
        $violations = $this->validator->validate($request);

        if ($violations->count() > 0) {
            $this->contactLogger->info('contact.validation_failed', [
                'request_id' => $requestId,
                'fields' => $this->fieldNames($violations),
            ]);

            return ContactSubmissionResult::validationError($this->formatViolations($violations));
        }

        $verdict = $this->turnstileVerifier->verify(
            $request->turnstileToken,
            $httpRequest->getClientIp() ?? 'unknown',
        );

        if (!$verdict->success) {
            $this->contactLogger->info('contact.turnstile_rejected', [
                'request_id' => $requestId,
                'reason' => $verdict->reason,
            ]);

            return ContactSubmissionResult::turnstileRejected();
        }

        $this->sender->send($request, $requestId);

        $this->contactLogger->info('contact.submitted', [
            'request_id' => $requestId,
            'project_type' => $request->projectType,
        ]);

        return ContactSubmissionResult::accepted();
    }

    /**
     * @return array<string, list<string>>
     */
    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $property = $violation->getPropertyPath();
            $errors[$property][] = (string) $violation->getMessage();
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function fieldNames(ConstraintViolationListInterface $violations): array
    {
        $names = [];

        foreach ($violations as $violation) {
            $names[] = $violation->getPropertyPath();
        }

        return array_values(array_unique($names));
    }
}
