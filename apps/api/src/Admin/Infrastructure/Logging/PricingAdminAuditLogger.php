<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Logging;

use App\Admin\Domain\AdminUser;
use Psr\Log\LoggerInterface;

/**
 * Loggeur d'audit pour les actions d'administration des grilles tarifaires (EST-8).
 *
 * Événements structurés, sans PII :
 *   - `admin.pricing.created`       : brouillon créé par clonage de la version publiée ;
 *   - `admin.pricing.updated`       : brouillon modifié ;
 *   - `admin.pricing.published`     : brouillon publié (ancienne version archivée) ;
 *   - `admin.pricing.deleted`       : brouillon supprimé ;
 *   - `admin.pricing.action_failed` : action refusée (CSRF, transition invalide, etc.) ;
 *   - `admin.pricing.rate_limited`  : action refusée par le limiteur.
 *
 * L'identifiant admin dans les logs est l'UUID uniquement — email et displayName
 * restent hors du canal de log.
 */
final class PricingAdminAuditLogger
{
    public function __construct(
        private readonly LoggerInterface $adminLogger,
    ) {}

    public function created(AdminUser $admin, string $configId, string $version): void
    {
        $this->adminLogger->info('admin.pricing.created', [
            'admin_id'  => $admin->id()->toRfc4122(),
            'config_id' => $configId,
            'version'   => $version,
        ]);
    }

    public function updated(AdminUser $admin, string $configId, string $version): void
    {
        $this->adminLogger->info('admin.pricing.updated', [
            'admin_id'  => $admin->id()->toRfc4122(),
            'config_id' => $configId,
            'version'   => $version,
        ]);
    }

    public function published(AdminUser $admin, string $configId, string $version, ?string $archivedConfigId): void
    {
        $this->adminLogger->info('admin.pricing.published', [
            'admin_id'           => $admin->id()->toRfc4122(),
            'config_id'          => $configId,
            'version'            => $version,
            'archived_config_id' => $archivedConfigId,
        ]);
    }

    public function deleted(AdminUser $admin, string $configId, string $version): void
    {
        $this->adminLogger->info('admin.pricing.deleted', [
            'admin_id'  => $admin->id()->toRfc4122(),
            'config_id' => $configId,
            'version'   => $version,
        ]);
    }

    public function actionFailed(AdminUser $admin, string $action, string $reasonCode, ?string $configId = null): void
    {
        $context = [
            'admin_id' => $admin->id()->toRfc4122(),
            'action'   => $action,
            'reason'   => $reasonCode,
        ];
        if ($configId !== null) {
            $context['config_id'] = $configId;
        }
        $this->adminLogger->warning('admin.pricing.action_failed', $context);
    }

    public function rateLimited(AdminUser $admin, string $action, int $retryAfterSeconds): void
    {
        $this->adminLogger->warning('admin.pricing.rate_limited', [
            'admin_id'            => $admin->id()->toRfc4122(),
            'action'              => $action,
            'retry_after_seconds' => $retryAfterSeconds,
        ]);
    }
}
