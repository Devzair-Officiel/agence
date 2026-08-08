<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Query;

/**
 * Page paginée pour l'écran d'administration des médias.
 *
 * `perPage` est fixé côté contrôleur (20 en 9A) ; `page` est clampée à ≥ 1.
 * Le total autorise la génération d'une pagination simple « précédent /
 * suivant » sans exposer d'ID incrémental.
 */
final class AdminMediaListPage
{
    /**
     * @param list<AdminMediaListItem> $items
     * @param int<1, max>              $page
     * @param int<1, max>              $perPage
     * @param int<0, max>              $total
     */
    public function __construct(
        public readonly array $items,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
    ) {
    }

    /**
     * @return int<1, max>
     */
    public function lastPage(): int
    {
        if ($this->total === 0) {
            return 1;
        }

        return (int) ceil($this->total / $this->perPage);
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->lastPage();
    }
}
