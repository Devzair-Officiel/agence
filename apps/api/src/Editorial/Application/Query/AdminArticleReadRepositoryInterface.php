<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\ArticleStatus;
use Symfony\Component\Uid\Uuid;

/**
 * Port de lecture dédié à l'administration éditoriale (Phase 8C3).
 *
 * Distinct de `ArticleRepositoryInterface` (domaine public) : celui-ci retourne
 * tous les statuts sans filtre `publishedAt <= now`, expose une pagination
 * paramétrable et un filtre optionnel par statut. Le séparer permet :
 *   - de préserver l'invariant du port public (« lectures publiques
 *     doublement filtrées ») ;
 *   - de faire évoluer indépendamment le modèle de lecture admin (tri,
 *     recherche, filtres) sans polluer le contrat public.
 *
 * Les implémentations retournent des vues plates (`AdminArticleListItem`,
 * `AdminArticleEditView`) — pas d'entité Doctrine — pour éviter que la couche
 * présentation ne bricole l'agrégat.
 */
interface AdminArticleReadRepositoryInterface
{
    /**
     * Retourne une page d'articles triés.
     *
     * Le tri secondaire par `id` (même direction) assure la stabilité de la
     * pagination à horodatages identiques. Les appelants qui n'ont pas besoin
     * de tri personnalisé obtiennent `updatedAt DESC, id DESC` par défaut.
     *
     * @param int<1, max> $page
     * @param int<1, max> $perPage
     *
     * @return list<AdminArticleListItem>
     */
    public function paginate(
        int $page,
        int $perPage,
        ?ArticleStatus $statusFilter,
        ArticleSortField $sortField = ArticleSortField::UpdatedAt,
        bool $sortAscending = false,
    ): array;

    public function count(?ArticleStatus $statusFilter): int;

    public function findForEdit(Uuid $id): ?AdminArticleEditView;
}
