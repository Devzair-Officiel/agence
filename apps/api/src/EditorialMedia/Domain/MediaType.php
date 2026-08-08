<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;

/**
 * Formats raster autorisés pour un média éditorial (Phase 9A).
 *
 * Volontairement restreint à JPEG / PNG / WebP :
 * - SVG refusé (surface XSS/XML inutile pour de l'illustration) ;
 * - GIF / BMP / TIFF / ICO / PDF / AVIF / HEIC refusés (formats non
 *   nécessaires au besoin éditorial actuel).
 *
 * L'énumération porte à la fois le MIME serveur (déterminé par `finfo`) et
 * l'extension canonique utilisée pour construire le chemin de stockage.
 * Le nom d'origine de l'utilisateur n'a jamais d'effet sur ces valeurs.
 */
enum MediaType: string
{
    case Jpeg = 'image/jpeg';
    case Png = 'image/png';
    case WebP = 'image/webp';

    /**
     * MIME → cas d'énumération. Retourne `null` pour tout MIME non autorisé.
     */
    public static function tryFromMime(string $mime): ?self
    {
        $normalized = strtolower(trim($mime));

        return self::tryFrom($normalized);
    }

    /**
     * MIME → cas d'énumération, lève si non supporté. Utilisé côté domaine
     * quand l'appelant garantit un contrôle amont (policy).
     */
    public static function fromMime(string $mime): self
    {
        $type = self::tryFromMime($mime);

        if ($type === null) {
            throw new EditorialMediaInvariantViolation(\sprintf(
                'Type MIME « %s » non supporté par le domaine EditorialMedia.',
                $mime,
            ));
        }

        return $type;
    }

    public function mime(): string
    {
        return $this->value;
    }

    /**
     * Extension canonique (sans point). Fixée côté domaine : on n'accepte
     * pas la casse ou la variante `.jpg` vs `.jpeg` de l'utilisateur.
     */
    public function extension(): string
    {
        return match ($this) {
            self::Jpeg => 'jpg',
            self::Png => 'png',
            self::WebP => 'webp',
        };
    }

    /**
     * @return list<string> Liste des MIME autorisés, pour messages/policies.
     */
    public static function allowedMimes(): array
    {
        return array_map(static fn (self $t): string => $t->value, self::cases());
    }
}
