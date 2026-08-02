<?php

declare(strict_types=1);

namespace App\Contact\Configuration;

/**
 * Anomalie détectée par {@see ContactConfigurationValidator}.
 *
 * `code` est un identifiant stable, exploitable en scripts d'ops. `message`
 * est une phrase FR courte pour humains. Ni l'un ni l'autre ne contient de
 * secret, DSN complet ou adresse email complète : la sortie de la commande
 * `app:contact:check` doit rester safe à coller dans un ticket.
 */
final readonly class ContactConfigurationIssue
{
    public const SEVERITY_ERROR = 'error';
    public const SEVERITY_WARNING = 'warning';

    public function __construct(
        public string $severity,
        public string $code,
        public string $field,
        public string $message,
    ) {
    }

    public static function error(string $code, string $field, string $message): self
    {
        return new self(self::SEVERITY_ERROR, $code, $field, $message);
    }

    public static function warning(string $code, string $field, string $message): self
    {
        return new self(self::SEVERITY_WARNING, $code, $field, $message);
    }

    public function isError(): bool
    {
        return $this->severity === self::SEVERITY_ERROR;
    }
}
