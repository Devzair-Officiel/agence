<?php

declare(strict_types=1);

namespace App\Editorial\Application\Media;

use Symfony\Component\Uid\Uuid;

/**
 * Port applicatif : autorise la diffusion publique d'un média.
 *
 * La règle métier de la Phase 9B est unique et binaire : un média n'est
 * servi publiquement (`GET /api/media/{id}`) QUE si au moins un `Article`
 * en statut `Published` (avec `publishedAt <= now`) le référence via
 * `hero_media_id`. Toute autre situation (média orphelin, article Draft ou
 * Archived, article publié dans le futur) doit se traduire par 404 pour ne
 * pas divulguer l'existence d'un média non publié.
 *
 * L'implémentation par défaut est Doctrine (adaptateur SQL brut : la
 * question ne charge aucune entité, un `EXISTS` suffit). Les tests
 * application/handler passent par un fake in-memory.
 */
interface PublicMediaGateInterface
{
    /**
     * Renvoie vrai si un article publié (à l'instant `$now`) référence ce
     * média comme image principale. Faux sinon — le contrôleur traduit en
     * 404 sans distinguer les raisons.
     */
    public function isReferencedByPublishedArticle(Uuid $mediaAssetId, \DateTimeImmutable $now): bool;
}
