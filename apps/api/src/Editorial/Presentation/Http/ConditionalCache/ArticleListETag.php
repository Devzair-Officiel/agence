<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Http\ConditionalCache;

use App\Editorial\Application\View\ArticleSummaryView;
use App\Editorial\Application\View\PaginationView;

/**
 * Calcul de l'ETag *faible* pour la liste paginée d'articles publiés.
 *
 * Le hash prend en compte :
 * - la pagination (page + perPage + total) — un même contenu article servi
 *   à des offsets différents doit avoir un ETag différent ;
 * - la signature « id + updated_at » de chaque item de la page — capture
 *   toute republication ou archivage qui modifierait la vue.
 *
 * Volontairement pas de `Last-Modified` sur la liste : la sémantique HTTP
 * de `Last-Modified` est celle d'une ressource unique, ce qui ne colle
 * pas à un tri paginé. L'ETag faible suffit — c'est aussi la préférence
 * confirmée dans le brief Phase 8B1.
 *
 * Historique :
 * - v1 : payload initial.
 * - v2 : Phase 9B — ajout de `hero_image` par item. Le payload change,
 *   l'ETag doit changer : d'où le bump.
 */
final class ArticleListETag
{
    public const CONTRACT_VERSION = 'v2';

    /**
     * @param list<ArticleSummaryView> $items
     */
    public static function forPage(array $items, PaginationView $pagination): string
    {
        $parts = [\sprintf(
            '%d|%d|%d|%s',
            $pagination->page,
            $pagination->perPage,
            $pagination->total,
            self::CONTRACT_VERSION,
        )];
        foreach ($items as $item) {
            $parts[] = $item->id.':'.$item->updatedAt;
        }

        return ArticleETag::hashOf(implode("\n", $parts));
    }
}
