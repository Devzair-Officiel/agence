<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\ExpertiseIdentifier;

/**
 * Query DTO — paramètres de la lecture publique paginée.
 *
 * Toute validation est appliquée ici et non dans le controller : cela
 * garantit que le handler reçoit toujours des bornes valides, y compris
 * s'il est appelé depuis un autre canal (CLI, futur worker, tests).
 *
 * Phase 10A2 : ajout du filtre facultatif `expertise` — la valeur brute
 * (chaîne) est convertie ici en `ExpertiseIdentifier` typé via l'enum
 * canonique du domaine, ce qui garantit que le repository ne reçoit
 * jamais qu'un cas d'enum (allowlist stricte, aucune interpolation
 * possible).
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
        public readonly ?ExpertiseIdentifier $expertise = null,
    ) {
    }

    public static function fromInputs(int $page, ?int $perPage, ?string $expertise = null): self
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

        $resolvedExpertise = null;
        if ($expertise !== null && $expertise !== '') {
            $resolvedExpertise = ExpertiseIdentifier::tryFrom($expertise);
            if ($resolvedExpertise === null) {
                throw new ArticleInvariantViolation(\sprintf(
                    'expertise doit être un identifiant d\'expertise connu : "%s" inconnu.',
                    $expertise,
                ));
            }
        }

        return new self($page, $resolvedPerPage, $resolvedExpertise);
    }
}
