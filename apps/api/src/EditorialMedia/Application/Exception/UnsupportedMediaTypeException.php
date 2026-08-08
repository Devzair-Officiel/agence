<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Exception;

/**
 * Le MIME détecté côté serveur (via `finfo`) n'appartient pas à la liste
 * `MediaType`. Volontairement distincte d'`EditorialMediaInvariantViolation`
 * pour que le contrôleur d'admin la traduise en 415 Unsupported Media Type
 * plutôt qu'en 400/500 générique.
 */
final class UnsupportedMediaTypeException extends \RuntimeException
{
    /**
     * @param list<string> $allowed
     */
    public static function forMime(string $mime, array $allowed): self
    {
        return new self(\sprintf(
            'Type MIME « %s » non autorisé. Types acceptés : %s.',
            $mime,
            implode(', ', $allowed),
        ));
    }
}
