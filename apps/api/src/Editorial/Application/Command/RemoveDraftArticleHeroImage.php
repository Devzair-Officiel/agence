<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use Symfony\Component\Uid\Uuid;

/**
 * Retire l'image principale d'un brouillon (aucun paramètre au-delà de
 * l'identifiant d'article — c'est une intention explicite).
 *
 * Cette command NE SUPPRIME PAS le `MediaAsset` en base ni le fichier
 * associé. La Phase 9B ne propose délibérément aucune suppression
 * permanente d'assets (voir brief §10) — les FK `RESTRICT` empêchent
 * une future suppression accidentelle d'un asset encore référencé, et
 * aucun garbage collector média n'est prévu dans cette phase.
 */
final class RemoveDraftArticleHeroImage
{
    public function __construct(
        public readonly Uuid $articleId,
    ) {
    }
}
