<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use Symfony\Component\Uid\Uuid;

/**
 * Requête ponctuelle pour la prévisualisation admin (Phase 8C4).
 *
 * L'UUID vient toujours de la route (jamais du corps du requête). Le
 * contrôleur `AdminArticlePreviewController` lève une 404 si l'ID est
 * malformé ou introuvable.
 */
final class GetAdminArticlePreview
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }
}
