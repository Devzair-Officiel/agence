<?php

declare(strict_types=1);

namespace App\Estimator\Infrastructure\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimit;

/**
 * Rate limiter dédié à POST /api/estimate/lead.
 *
 * Bucket `estimate_lead_ip` — plus strict que `estimate_ip` (10/min vs 30/min)
 * car chaque lead déclenche une persistance DB et un envoi e-mail.
 * Séparé du bucket estimateur pour ne pas impacter l'expérience de calcul.
 */
final class EstimateLeadRateLimiter
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
