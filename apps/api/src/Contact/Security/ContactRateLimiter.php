<?php

declare(strict_types=1);

namespace App\Contact\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimit;

/**
 * Enveloppe le RateLimiter Symfony pour l'endpoint /api/contact.
 *
 * Clé : IP cliente réelle (dernier hop de X-Forwarded-For, obtenu via
 * Request::getClientIp() une fois les trusted_proxies configurés).
 */
final class ContactRateLimiter
{
    public function __construct(
        private readonly RateLimiterFactory $limiterFactory,
    ) {
    }

    public function consume(Request $request): RateLimit
    {
        $ip = $request->getClientIp() ?? 'unknown';

        return $this->limiterFactory->create($ip)->consume(1);
    }
}
