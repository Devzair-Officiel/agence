<?php

declare(strict_types=1);

namespace App\Editorial\Application\Media;

use Symfony\Component\Uid\Uuid;

/**
 * DTO immuable décrivant les métadonnées d'un `MediaAsset` sous une forme
 * consommable par les read-models du contexte `Editorial`.
 *
 * N'expose délibérément QUE ce qui est utile aux vues admin/publique :
 *   - `id` : référence stable ;
 *   - `width` / `height` : nécessaires côté SEO/Open Graph et pour éviter
 *     un CLS côté rendu Nuxt ;
 *   - `mimeType` : posé côté serveur (jamais réinféré côté client) ;
 *   - `sizeBytes` : utile aux vues d'audit admin ;
 *   - `sha256` : posé pour construire un ETag stable côté route publique
 *     média (Checkpoint 2). Aucune fuite : le sha256 d'un média normalisé
 *     n'est pas un secret et le contrat public n'en dérive qu'un ETag.
 *
 * N'expose délibérément PAS :
 *   - `storageKey` — c'est un détail d'infrastructure filesystem ;
 *   - `originalFilename` — c'est du contexte admin uniquement, jamais public.
 */
final class MediaAssetDescriptor
{
    public function __construct(
        public readonly Uuid $id,
        public readonly int $width,
        public readonly int $height,
        public readonly string $mimeType,
        public readonly int $sizeBytes,
        public readonly string $sha256,
    ) {
    }
}
