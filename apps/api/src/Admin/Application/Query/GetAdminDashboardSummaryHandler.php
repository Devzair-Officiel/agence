<?php

declare(strict_types=1);

namespace App\Admin\Application\Query;

use App\Editorial\Application\Query\AdminArticleReadRepositoryInterface;
use App\Editorial\Domain\ArticleStatus;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;

/**
 * Agrège les indicateurs éditoriaux pour le tableau de bord admin (Phase R2).
 *
 * Réutilise les ports de lecture existants plutôt que d'introduire une requête
 * SQL parallèle :
 *   - `AdminArticleReadRepositoryInterface::count()` — compteurs par statut ;
 *   - `AdminArticleReadRepositoryInterface::paginate()` — liste récente triée
 *     `updatedAt DESC, id DESC`, limitée à RECENT_LIMIT entrées ;
 *   - `MediaAssetRepositoryInterface::count()` — total des médias.
 */
final class GetAdminDashboardSummaryHandler
{
    private const RECENT_LIMIT = 5;

    public function __construct(
        private readonly AdminArticleReadRepositoryInterface $articleRepository,
        private readonly MediaAssetRepositoryInterface $mediaRepository,
    ) {
    }

    public function __invoke(): AdminDashboardSummary
    {
        return new AdminDashboardSummary(
            published: $this->articleRepository->count(ArticleStatus::Published),
            drafts: $this->articleRepository->count(ArticleStatus::Draft),
            archived: $this->articleRepository->count(ArticleStatus::Archived),
            mediaCount: $this->mediaRepository->count(),
            recentArticles: $this->articleRepository->paginate(1, self::RECENT_LIMIT, null),
        );
    }
}
