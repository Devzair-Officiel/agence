<?php

declare(strict_types=1);

namespace App\Contact\Service;

/**
 * Résultat d'une exécution de SubmitContactMessage.
 * Le contrôleur convertit ce résultat en Response HTTP.
 */
final readonly class ContactSubmissionResult
{
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_VALIDATION_ERROR = 'validation_error';
    public const STATUS_TURNSTILE_REJECTED = 'turnstile_rejected';

    /**
     * @param array<string, list<string>> $errors
     */
    private function __construct(
        public string $status,
        public array $errors = [],
    ) {
    }

    public static function accepted(): self
    {
        return new self(self::STATUS_ACCEPTED);
    }

    /**
     * @param array<string, list<string>> $errors
     */
    public static function validationError(array $errors): self
    {
        return new self(self::STATUS_VALIDATION_ERROR, $errors);
    }

    public static function turnstileRejected(): self
    {
        return new self(self::STATUS_TURNSTILE_REJECTED);
    }
}
