<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use Symfony\Component\Uid\Uuid;

/**
 * Chemin logique d'un variant WebP dans le stockage privé.
 *
 * Format : `{uuid-rfc4122}/(card|hero).webp`.
 *
 * Distinct de `StorageKey` (qui porte `original.{ext}`) pour éviter de
 * faire muter le pattern de validation de ce VO stable. Les invariants sont
 * identiques (pas de traversal, forme canonique serveur uniquement) mais le
 * suffix diffère.
 */
final class VariantStorageKey
{
    private const PATTERN = '~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/(card|hero)\.webp$~';

    private function __construct(private readonly string $value)
    {
    }

    public static function forVariant(Uuid $id, ImageVariant $variant): self
    {
        return new self(\sprintf('%s/%s.webp', $id->toRfc4122(), $variant->value));
    }

    public static function fromString(string $raw): self
    {
        if (preg_match(self::PATTERN, $raw) !== 1) {
            throw new EditorialMediaInvariantViolation(\sprintf(
                'La clé de variant « %s » ne respecte pas le format {uuid}/(card|hero).webp.',
                $raw,
            ));
        }

        return new self($raw);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
