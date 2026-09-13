<?php

declare(strict_types=1);

namespace App\Editorial\Application\View;

use App\Editorial\Application\Media\MediaAssetDescriptor;
use App\Editorial\Domain\ArticleHeroImage;

/**
 * DTO public d'image principale (Phase 9B/9C).
 *
 * Contrat public exposé au front Nuxt via `/api/resources` et
 * `/api/resources/{slug}` :
 *   - `url`      : original → `/api/media/{uuid}` (legacy + fallback).
 *   - `alt`      : texte alternatif obligatoire (1..300 chars).
 *   - `width`    : dimensions de l'original normalisé (pour CLS).
 *   - `height`   : idem.
 *   - `mimeType` : posé côté serveur.
 *   - `card`     : variant WebP card, null pour les assets sans variants.
 *   - `hero`     : variant WebP hero, null pour les assets sans variants.
 *
 * L'URL original est conservée pour la compatibilité avec les anciens assets
 * et pour le `<meta og:image>`. Les pages publiques utilisent `card` (listes)
 * et `hero` (détail) dès qu'ils sont présents.
 */
final class PublicArticleImageView
{
    public function __construct(
        public readonly string $url,
        public readonly string $alt,
        public readonly int $width,
        public readonly int $height,
        public readonly string $mimeType,
        public readonly ?PublicArticleImageVariantView $card = null,
        public readonly ?PublicArticleImageVariantView $hero = null,
    ) {
    }

    public static function fromDescriptor(ArticleHeroImage $hero, MediaAssetDescriptor $descriptor): self
    {
        $uuid = $descriptor->id->toRfc4122();

        $card = $descriptor->card !== null
            ? new PublicArticleImageVariantView(
                url: $descriptor->card->url,
                width: $descriptor->card->width,
                height: $descriptor->card->height,
            )
            : null;

        $heroVariant = $descriptor->hero !== null
            ? new PublicArticleImageVariantView(
                url: $descriptor->hero->url,
                width: $descriptor->hero->width,
                height: $descriptor->hero->height,
            )
            : null;

        return new self(
            url: '/api/media/' . $uuid,
            alt: $hero->altText(),
            width: $descriptor->width,
            height: $descriptor->height,
            mimeType: $descriptor->mimeType,
            card: $card,
            hero: $heroVariant,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'url'       => $this->url,
            'alt'       => $this->alt,
            'width'     => $this->width,
            'height'    => $this->height,
            'mime_type' => $this->mimeType,
            'card'      => $this->card?->toArray(),
            'hero'      => $this->hero?->toArray(),
        ];
    }
}
