<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Logging;

use App\Admin\Domain\AdminUser;
use Psr\Log\LoggerInterface;

/**
 * Loggeur d'audit dédié aux actions médias admin (Phase 9A).
 *
 * Événements structurés, sans PII, sans nom d'origine, sans chemin absolu :
 *   - `admin.media.uploaded`          : téléversement réussi ;
 *   - `admin.media.upload_failed`     : téléversement refusé (validation,
 *                                       décodage, dimensions, stockage) ;
 *   - `admin.media.upload_rate_limited` : refus par le limiteur ;
 *   - `admin.media.previewed`         : rendu binaire renvoyé par le contrôleur
 *                                       de prévisualisation (utile pour
 *                                       corréler un accès admin à un asset).
 *
 * Le seul identifiant de l'admin dans les logs est son UUID. Le SHA-256 est
 * tronqué à ses 12 premiers caractères — assez pour corréler avec l'entité
 * `MediaAsset` sans exposer d'empreinte complète.
 */
final class MediaAdminAuditLogger
{
    public function __construct(
        private readonly LoggerInterface $adminLogger,
    ) {
    }

    public function uploaded(
        AdminUser $admin,
        string $assetId,
        string $mime,
        int $sizeBytes,
        int $width,
        int $height,
        string $sha256,
    ): void {
        $this->adminLogger->info('admin.media.uploaded', [
            'admin_id' => $admin->id()->toRfc4122(),
            'asset_id' => $assetId,
            'mime' => $mime,
            'size_bytes' => $sizeBytes,
            'width' => $width,
            'height' => $height,
            'sha256_prefix' => substr($sha256, 0, 12),
        ]);
    }

    public function uploadFailed(AdminUser $admin, string $reasonCode): void
    {
        $this->adminLogger->warning('admin.media.upload_failed', [
            'admin_id' => $admin->id()->toRfc4122(),
            'reason' => $reasonCode,
        ]);
    }

    public function uploadRateLimited(AdminUser $admin, int $retryAfterSeconds): void
    {
        $this->adminLogger->warning('admin.media.upload_rate_limited', [
            'admin_id' => $admin->id()->toRfc4122(),
            'retry_after_seconds' => $retryAfterSeconds,
        ]);
    }

    public function previewed(AdminUser $admin, string $assetId, string $mime): void
    {
        $this->adminLogger->info('admin.media.previewed', [
            'admin_id' => $admin->id()->toRfc4122(),
            'asset_id' => $assetId,
            'mime' => $mime,
        ]);
    }
}
