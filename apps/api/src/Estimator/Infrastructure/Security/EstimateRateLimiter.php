<?php

declare(strict_types=1);

namespace App\Estimator\Infrastructure\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimit;

/**
 * Enveloppe le RateLimiter Symfony pour l'endpoint POST /api/estimate.
 *
 * Bucket dédié (estimate_ip) — indépendant du bucket Contact pour que
 * le trafic estimateur n'épuise jamais les tokens du formulaire de contact
 * et vice-versa.
 *
 * Clé : IP cliente réelle obtenue via Request::getClientIp() une fois les
 * trusted_proxies configurés (Caddy → X-Forwarded-For → Symfony).
 */
final class EstimateRateLimiter
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
