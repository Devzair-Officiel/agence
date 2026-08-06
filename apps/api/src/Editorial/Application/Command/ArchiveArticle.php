<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use Symfony\Component\Uid\Uuid;

/**
 * Archive un article existant, adressé par son identifiant stable.
 *
 * L'archivage retire l'article du catalogue public (non listé, non
 * résolvable par slug), tout en conservant sa date historique de
 * publication. Idempotent : archiver un article déjà archivé ne fait rien.
 */
final class ArchiveArticle
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }
}
