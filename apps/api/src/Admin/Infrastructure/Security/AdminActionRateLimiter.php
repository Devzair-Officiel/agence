<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Security;

use App\Admin\Domain\AdminUser;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Enveloppe deux limiteurs Symfony (`admin_write`, `admin_publish`) et
 * indexe chaque compteur par UUID admin — deux administrateurs ont donc
 * des compteurs strictement indépendants.
 *
 * Le service ne consomme jamais tacitement le token : il expose
 * `consumeWrite()` / `consumePublish()` qui renvoient le `RateLimit` brut.
 * Les contrôleurs décident du comportement (429 avec `Retry-After` si
 * `!isAccepted()`, action normale sinon) et une consommation refusée
 * n'entraîne aucune mutation persistante — c'est le contrôleur qui garantit
 * l'ordre (« vérifier avant d'agir »).
 */
final class AdminActionRateLimiter
{
    public function __construct(
        private readonly RateLimiterFactory $adminWriteLimiter,
        private readonly RateLimiterFactory $adminPublishLimiter,
    ) {
    }

    public function consumeWrite(AdminUser $admin): RateLimit
    {
        return $this->adminWriteLimiter
            ->create($this->key('admin_write', $admin))
            ->consume(1);
    }

    public function consumePublish(AdminUser $admin): RateLimit
    {
        return $this->adminPublishLimiter
            ->create($this->key('admin_publish', $admin))
            ->consume(1);
    }

    private function key(string $limiterName, AdminUser $admin): string
    {
        return \sprintf('%s:%s', $limiterName, $admin->id()->toRfc4122());
    }
}
