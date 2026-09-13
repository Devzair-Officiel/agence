<?php

declare(strict_types=1);

namespace App\Editorial\Application\Media;

use App\EditorialMedia\Domain\ImageVariant;
use Symfony\Component\Uid\Uuid;

/**
 * Port applicatif : ouvre un flux public sur un variant WebP d'un média
 * éditorial (Phase 9C).
 *
 * Séparé de `PublicMediaStreamerInterface` (original) pour respecter ISP :
 * les appelants de l'original n'ont pas à dépendre de la logique variant.
 *
 * Renvoie `null` si le média ou le variant n'existe pas, ou si le fichier
 * physique est illisible. Le contrôleur traduit en 404 dans tous ces cas.
 */
interface PublicMediaVariantStreamerInterface
{
    public function openVariant(Uuid $mediaAssetId, ImageVariant $variant): ?PublicMediaResource;
}
