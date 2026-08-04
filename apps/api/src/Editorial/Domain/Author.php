<?php

declare(strict_types=1);

namespace App\Editorial\Domain;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;

/**
 * Value object auteur.
 *
 * Immutable, comparable par valeur. On distingue une organisation (par
 * défaut : « Devzair ») d'une personne nominative, sans jamais introduire
 * de photo ou de biographie factices — l'API expose juste le nom et le
 * type, à charge du front de rendre différemment.
 */
final class Author
{
    private const NAME_MIN = 2;
    private const NAME_MAX = 120;

    private function __construct(
        private readonly string $name,
        private readonly AuthorType $type,
    ) {
    }

    public static function organization(string $name): self
    {
        return self::create($name, AuthorType::Organization);
    }

    public static function person(string $name): self
    {
        return self::create($name, AuthorType::Person);
    }

    private static function create(string $name, AuthorType $type): self
    {
        $trimmed = trim($name);
        $length = mb_strlen($trimmed);

        if ($length < self::NAME_MIN || $length > self::NAME_MAX) {
            throw new ArticleInvariantViolation(\sprintf(
                'Le nom d\'auteur doit contenir entre %d et %d caractères.',
                self::NAME_MIN,
                self::NAME_MAX,
            ));
        }

        return new self($trimmed, $type);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): AuthorType
    {
        return $this->type;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name && $this->type === $other->type;
    }
}
