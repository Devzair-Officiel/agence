<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;

/**
 * Empreinte SHA-256 (64 caractères hex minuscules) du fichier normalisé
 * réellement stocké. Sert d'intégrité et de clé de diagnostic. Aucun
 * traitement de déduplication automatique n'est mis en place en 9A —
 * deux téléversements identiques restent deux assets distincts.
 */
final class Sha256
{
    private const PATTERN = '~^[0-9a-f]{64}$~';

    private function __construct(private readonly string $value)
    {
    }

    public static function fromString(string $raw): self
    {
        $normalized = strtolower(trim($raw));

        if (preg_match(self::PATTERN, $normalized) !== 1) {
            throw new EditorialMediaInvariantViolation(
                'Une empreinte SHA-256 doit contenir exactement 64 caractères hexadécimaux.',
            );
        }

        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
