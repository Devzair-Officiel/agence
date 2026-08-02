<?php

declare(strict_types=1);

namespace App\Contact\Security;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Vérifie un token Turnstile via l'endpoint Siteverify de Cloudflare.
 *
 * Sécurité :
 * - le secret n'est jamais loggué ;
 * - le token n'est jamais loggué ;
 * - un échec réseau ou HTTP retourne un verdict d'échec (fail-closed) :
 *   pas de secours silencieux vers "accepté".
 */
final class CloudflareTurnstileVerifier implements TurnstileVerifierInterface
{
    private const SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        private readonly string $secret,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function verify(?string $token, string $remoteIp): TurnstileVerdict
    {
        if ($token === null || $token === '') {
            return TurnstileVerdict::failure('missing_token');
        }

        try {
            $response = $this->httpClient->request('POST', self::SITEVERIFY_URL, [
                'body' => [
                    'secret' => $this->secret,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ],
                'timeout' => 5,
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logger->warning('turnstile.http_error', [
                    'status' => $response->getStatusCode(),
                ]);

                return TurnstileVerdict::failure('http_error');
            }

            $data = $response->toArray(false);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->warning('turnstile.transport_error', [
                'error' => $exception::class,
            ]);

            return TurnstileVerdict::failure('transport_error');
        }

        $success = (bool) ($data['success'] ?? false);

        if (!$success) {
            return TurnstileVerdict::failure('rejected');
        }

        return TurnstileVerdict::ok();
    }
}
