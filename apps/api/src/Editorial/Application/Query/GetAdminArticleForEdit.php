<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use Symfony\Component\Uid\Uuid;

/**
 * Requête ponctuelle pour l'écran d'édition admin. L'UUID vient de la route ;
 * les contrôleurs lèvent 404 si l'article n'existe pas.
 */
final class GetAdminArticleForEdit
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }
}
