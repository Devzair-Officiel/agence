<?php

declare(strict_types=1);

namespace App\Editorial\Domain;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;

/**
 * Slug URL-safe, minuscule, séparé par tirets.
 *
 * Contraintes :
 * - 3 à 120 caractères.
 * - Uniquement `[a-z0-9]` séparés par un tiret unique.
 * - Ni tiret initial/final, ni double tiret.
 *
 * L'unicité applicative est portée par le repository (contrainte UNIQUE en base).
 */
final class ArticleSlug implements \Stringable
{
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    private const MIN_LENGTH = 3;
    private const MAX_LENGTH = 120;

    private function __construct(
        private readonly string $value,
    ) {
    }

    public static function fromString(string $raw): self
    {
        $trimmed = trim($raw);
        $length = \strlen($trimmed);

        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new ArticleInvariantViolation(\sprintf(
                'Le slug doit contenir entre %d et %d caractères, "%s" en contient %d.',
                self::MIN_LENGTH,
                self::MAX_LENGTH,
                $trimmed,
                $length,
            ));
        }

        if (preg_match(self::PATTERN, $trimmed) !== 1) {
            throw new ArticleInvariantViolation(\sprintf(
                'Slug invalide "%s" : uniquement [a-z0-9] séparés par un tiret unique.',
                $trimmed,
            ));
        }

        return new self($trimmed);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
