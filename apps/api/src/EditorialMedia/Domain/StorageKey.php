<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use Symfony\Component\Uid\Uuid;

/**
 * Chemin logique du fichier normalisé dans le stockage privé. Toujours
 * généré côté serveur à partir d'un UUID interne et de l'extension
 * canonique du type MIME contrôlé — jamais dérivé du nom d'origine.
 *
 * Format retenu : `{uuid-rfc4122}/original.{ext}`.
 *
 * - Le sous-répertoire par UUID isole les variantes futures (Phase 9B :
 *   `avatar-320.webp`, `og-1200.jpg`…) sans casser la structure existante.
 * - Aucun caractère utilisateur : le validateur refuse tout ce qui n'est
 *   pas la forme canonique afin de fermer toute vecteur de path traversal
 *   même si un appelant fabrique une clé à la main.
 */
final class StorageKey
{
    private const PATTERN = '~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/original\.(jpg|png|webp)$~';

    private function __construct(private readonly string $value)
    {
    }

    public static function forNewAsset(Uuid $id, MediaType $type): self
    {
        return new self(\sprintf('%s/original.%s', $id->toRfc4122(), $type->extension()));
    }

    public static function fromString(string $raw): self
    {
        if (preg_match(self::PATTERN, $raw) !== 1) {
            throw new EditorialMediaInvariantViolation(\sprintf(
                'La clé de stockage « %s » ne respecte pas le format {uuid}/original.{jpg|png|webp}.',
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
