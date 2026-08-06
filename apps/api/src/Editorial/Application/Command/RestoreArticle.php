<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use Symfony\Component\Uid\Uuid;

/**
 * Restaure un article archivé vers le statut `Draft`, adressé par son
 * identifiant stable. Retire la date de publication (l'article redevient
 * un brouillon éditable). Refusé si l'article est encore publié.
 */
final class RestoreArticle
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }
}
