<?php

declare(strict_types=1);

namespace App\Estimator\Infrastructure\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimit;

/**
 * Rate limiter dédié à POST /api/estimate/partnership.
 *
 * Bucket `estimate_partnership_ip` — plus strict que `estimate_ip` car chaque
 * proposition déclenche une persistance DB. Séparé de `estimate_lead_ip` pour
 * que les deux flux restent indépendants.
 */
final class EstimatePartnershipRateLimiter
{
    public function __construct(
        private readonly RateLimiterFactory $limiterFactory,
    ) {}

    public function consume(Request $request): RateLimit
    {
        $ip = $request->getClientIp() ?? 'unknown';

        return $this->limiterFactory->create($ip)->consume(1);
    }
}
