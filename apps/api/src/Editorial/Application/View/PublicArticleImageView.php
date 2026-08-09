<?php

declare(strict_types=1);

namespace App\Editorial\Application\View;

use App\Editorial\Application\Media\MediaAssetDescriptor;
use App\Editorial\Domain\ArticleHeroImage;

/**
 * DTO public d'image principale (Phase 9B).
 *
 * Contrat public exposé au front Nuxt via `/api/resources` et
 * `/api/resources/{slug}` :
 *   - `url` : chemin absolu côté API (`/api/media/{uuid}`) — le front l'assemble
 *     avec `runtimeConfig.public.apiBase` s'il pointe vers un domaine distinct ;
 *     ici on renvoie déjà `/api/…` pour rester utilisable tel quel en dev où
 *     l'API vit derrière le même reverse proxy ;
 *   - `alt` : texte alternatif obligatoire, borné par le VO
 *     `ArticleHeroImage` (1..300 chars) ;
 *   - `width` / `height` : dimensions réelles du média normalisé — permettent
 *     au front de réserver le layout (évite CLS) et de servir un
 *     `<img width height>` conforme aux Web Vitals ;
 *   - `mimeType` : posé côté serveur, jamais réinféré côté client.
 *
 * Volontairement PAS exposés :
 *   - `sizeBytes` — détail infra, non pertinent pour un consommateur front ;
 *   - `sha256` — utile en interne pour l'ETag, pas au client public ;
 *   - `mediaAssetId` UUID brut — le client n'a besoin que de l'URL, qui
 *     porte déjà l'identifiant.
 */
final class PublicArticleImageView
{
    public function __construct(
        public readonly string $url,
        public readonly string $alt,
        public readonly int $width,
        public readonly int $height,
        public readonly string $mimeType,
    ) {
    }

    /**
     * Assemble le DTO en associant le VO domaine (`ArticleHeroImage`) et le
     * descripteur de média (dimensions / mime). Le contrat public ne franchit
     * l'ACL qu'à ce point unique — le front ne voit plus jamais l'UUID nu.
     *
     * L'URL suit strictement `/api/media/{uuid}`, un lien stable sous le
     * pattern `^/api/media/[0-9a-f-]{36}$`. Aucun paramètre de query, aucune
     * variation par device ou par variant : le média normalisé est unique.
     */
    public static function fromDescriptor(ArticleHeroImage $hero, MediaAssetDescriptor $descriptor): self
    {
        return new self(
            url: '/api/media/'.$descriptor->id->toRfc4122(),
            alt: $hero->altText(),
            width: $descriptor->width,
            height: $descriptor->height,
            mimeType: $descriptor->mimeType,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'alt' => $this->alt,
            'width' => $this->width,
            'height' => $this->height,
            'mime_type' => $this->mimeType,
        ];
    }
}
