<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Query;

/**
 * Requête paginée pour la liste admin. `page` et `perPage` sont bornés en
 * amont par le contrôleur.
 */
final class ListAdminMedia
{
    /**
     * @param int<1, max> $page
     * @param int<1, max> $perPage
     */
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
    ) {
    }
}
