<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;

/**
 * Query DTO — paramètres de la lecture publique paginée.
 *
 * Toute validation est appliquée ici et non dans le controller : cela
 * garantit que le handler reçoit toujours des bornes valides, y compris
 * s'il est appelé depuis un autre canal (CLI, futur worker, tests).
 */
final class ListPublishedArticles
{
    public const DEFAULT_PER_PAGE = 10;
    public const MAX_PER_PAGE = 50;

    /**
     * @param int<1, max> $page
     * @param int<1, 50>  $perPage
     */
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
    ) {
    }

    public static function fromInputs(int $page, ?int $perPage): self
    {
        if ($page < 1) {
            throw new ArticleInvariantViolation('Le numéro de page doit être ≥ 1.');
        }

        $resolvedPerPage = $perPage ?? self::DEFAULT_PER_PAGE;
        if ($resolvedPerPage < 1 || $resolvedPerPage > self::MAX_PER_PAGE) {
            throw new ArticleInvariantViolation(\sprintf(
                'per_page doit être entre 1 et %d.',
                self::MAX_PER_PAGE,
            ));
        }

        return new self($page, $resolvedPerPage);
    }
}
