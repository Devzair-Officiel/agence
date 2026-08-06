<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Logging;

use App\Admin\Domain\AdminUser;
use App\Editorial\Domain\ArticleStatus;
use Psr\Log\LoggerInterface;

/**
 * Loggeur d'audit pour les actions éditoriales admin (Phase 8C3).
 *
 * Événements structurés, sans PII, sans Markdown, sans HTML :
 *   - `admin.article.created`         : création réussie ;
 *   - `admin.article.updated`         : mutation qui a effectivement changé
 *                                       au moins un champ ;
 *   - `admin.article.update_noop`     : mutation soumise mais tous les
 *                                       champs étaient identiques ;
 *   - `admin.article.published`       : passage Draft → Published ;
 *   - `admin.article.archived`        : passage vers Archived (retire du
 *                                       catalogue public) ;
 *   - `admin.article.restored`        : Archived → Draft ;
 *   - `admin.article.action_failed`   : action refusée (invariant, statut
 *                                       incompatible, slug déjà pris,
 *                                       validation Markdown, etc.) ;
 *   - `admin.article.rate_limited`    : action refusée par le limiteur.
 *
 * Le seul identifiant de l'admin dans les logs est son UUID. L'email et le
 * displayName restent hors du canal `admin` (ils vivent uniquement en base
 * pour identification humaine — jamais dans stderr/JSON).
 */
final class EditorialAdminAuditLogger
{
    public function __construct(
        private readonly LoggerInterface $adminLogger,
    ) {
    }

    public function created(AdminUser $admin, string $articleId, string $slug): void
    {
        $this->adminLogger->info('admin.article.created', [
            'admin_id' => $admin->id()->toRfc4122(),
            'article_id' => $articleId,
            'slug' => $slug,
        ]);
    }

    public function updated(AdminUser $admin, string $articleId): void
    {
        $this->adminLogger->info('admin.article.updated', [
            'admin_id' => $admin->id()->toRfc4122(),
            'article_id' => $articleId,
        ]);
    }

    public function updateNoop(AdminUser $admin, string $articleId): void
    {
        $this->adminLogger->info('admin.article.update_noop', [
            'admin_id' => $admin->id()->toRfc4122(),
            'article_id' => $articleId,
        ]);
    }

    public function published(AdminUser $admin, string $articleId, string $slug): void
    {
        $this->adminLogger->info('admin.article.published', [
            'admin_id' => $admin->id()->toRfc4122(),
            'article_id' => $articleId,
            'slug' => $slug,
        ]);
    }

    public function archived(AdminUser $admin, string $articleId, ArticleStatus $priorStatus): void
    {
        $this->adminLogger->info('admin.article.archived', [
            'admin_id' => $admin->id()->toRfc4122(),
            'article_id' => $articleId,
            'prior_status' => $priorStatus->value,
        ]);
    }

    public function restored(AdminUser $admin, string $articleId): void
    {
        $this->adminLogger->info('admin.article.restored', [
            'admin_id' => $admin->id()->toRfc4122(),
            'article_id' => $articleId,
        ]);
    }

    public function actionFailed(AdminUser $admin, string $action, string $reasonCode, ?string $articleId = null): void
    {
        $context = [
            'admin_id' => $admin->id()->toRfc4122(),
            'action' => $action,
            'reason' => $reasonCode,
        ];
        if ($articleId !== null) {
            $context['article_id'] = $articleId;
        }
        $this->adminLogger->warning('admin.article.action_failed', $context);
    }

    public function rateLimited(AdminUser $admin, string $action, int $retryAfterSeconds): void
    {
        $this->adminLogger->warning('admin.article.rate_limited', [
            'admin_id' => $admin->id()->toRfc4122(),
            'action' => $action,
            'retry_after_seconds' => $retryAfterSeconds,
        ]);
    }
}
