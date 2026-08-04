<?php

declare(strict_types=1);

namespace App\Editorial\Application\View;

/**
 * DTO de pagination exposé par l'API publique.
 *
 * Séparé du repository pour garantir un contrat JSON stable indépendamment
 * du moteur de persistance. `totalPages` est calculé côté application, pas
 * en base — pour éviter un appel supplémentaire au cas où on cache
 * `countPublished()` plus tard.
 */
final class PaginationView
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $totalPages,
    ) {
    }

    public static function fromCount(int $page, int $perPage, int $total): self
    {
        $totalPages = $total === 0 ? 0 : (int) ceil($total / $perPage);

        return new self($page, $perPage, $total, $totalPages);
    }

    /**
     * @return array{page: int, per_page: int, total: int, total_pages: int}
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'total_pages' => $this->totalPages,
        ];
    }
}
