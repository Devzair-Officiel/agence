<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Security;

use App\Admin\Domain\AdminUser;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Limiteur d'upload de médias, indexé par UUID d'admin. Deux administrateurs
 * ont donc des compteurs strictement indépendants. Le contrôleur consomme UN
 * token AVANT d'exécuter le use case ; si le token est refusé, il renvoie
 * 429 avec `Retry-After` et n'appelle jamais le handler — aucun fichier n'est
 * décodé ni persisté.
 *
 * Voir §11 du brief Phase 9A : 20 uploads / 10 minutes / admin par défaut,
 * ajustables via `ADMIN_MEDIA_UPLOAD_LIMIT` et `ADMIN_MEDIA_UPLOAD_INTERVAL`.
 */
final class AdminMediaUploadRateLimiter
{
    public function __construct(
        private readonly RateLimiterFactory $limiterFactory,
    ) {
    }

    public function consume(AdminUser $admin): RateLimit
    {
        return $this->limiterFactory
            ->create(\sprintf('admin_media_upload:%s', $admin->id()->toRfc4122()))
            ->consume(1);
    }
}
