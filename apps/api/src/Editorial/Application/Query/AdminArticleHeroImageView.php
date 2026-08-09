<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

/**
 * Vue plate d'une image principale d'article pour l'écran d'édition admin
 * ou la prévisualisation admin.
 *
 * N'expose que ce qui est réellement rendu à l'écran :
 *   - `mediaId` : identifiant RFC 4122 — utilisé pour générer l'URL de
 *     prévisualisation privée (`path('admin_media_preview', {id})`) ;
 *   - `altText` : texte alternatif éditorial (jamais dérivé du filename) ;
 *   - `width`, `height` : dimensions intrinsèques — permettent aux
 *     templates admin d'éviter tout CLS lors du chargement de la miniature ;
 *   - `mimeType` : MIME serveur fixé côté normalisation (jamais client).
 *
 * N'expose PAS :
 *   - `storageKey` (détail filesystem) ;
 *   - `sha256` (utile côté route publique 9B, pas ici) ;
 *   - `originalFilename` (visible dans la bibliothèque, pas dans le rendu
 *     d'article).
 *
 * `mediaId` est stocké comme chaîne RFC 4122 pour éviter que la couche
 * template Twig ait à manipuler un objet `Uuid` (KISS côté vue).
 */
final class AdminArticleHeroImageView
{
    public function __construct(
        public readonly string $mediaId,
        public readonly string $altText,
        public readonly int $width,
        public readonly int $height,
        public readonly string $mimeType,
    ) {
    }
}
