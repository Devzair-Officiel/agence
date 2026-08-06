<?php

declare(strict_types=1);

namespace App\Admin\Domain;

use App\Admin\Domain\Exception\AdminUserInvariantViolation;

/**
 * Value object encapsulant un email d'administrateur et sa forme normalisée.
 *
 * La forme normalisée (`normalized()`) est celle utilisée pour la comparaison
 * en base et l'identifiant Security. La forme originale (`display()`) sert à
 * l'affichage (mais reste sans valeur d'authentification).
 */
final class AdminEmail
{
    private const MAX_LENGTH = 180;
    private const PATTERN = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

    private function __construct(
        private readonly string $original,
        private readonly string $normalized,
    ) {
    }

    public static function fromString(string $email): self
    {
        $trimmed = trim($email);

        if ($trimmed === '') {
            throw new AdminUserInvariantViolation('L\'email ne peut pas être vide.');
        }

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw new AdminUserInvariantViolation(\sprintf(
                'L\'email dépasse la longueur maximale de %d caractères.',
                self::MAX_LENGTH,
            ));
        }

        if (preg_match(self::PATTERN, $trimmed) !== 1) {
            throw new AdminUserInvariantViolation('L\'email n\'a pas un format valide.');
        }

        return new self($trimmed, mb_strtolower($trimmed));
    }

    public function display(): string
    {
        return $this->original;
    }

    public function normalized(): string
    {
        return $this->normalized;
    }
}
