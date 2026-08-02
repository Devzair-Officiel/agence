<?php

declare(strict_types=1);

namespace App\Contact\Security;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Choisit l'implémentation de TurnstileVerifierInterface selon l'env.
 *
 * Garde-fou : si TURNSTILE_ENABLED=true mais TURNSTILE_SECRET vide, on jette
 * une exception au boot pour refuser un démarrage silencieusement vulnérable.
 * Symétriquement, si le site est indexable (donc en prod) et Turnstile
 * désactivé, on log un warning haut niveau (voir Kernel).
 */
final class TurnstileVerifierFactory
{
    public function __construct(
        private readonly bool $enabled,
        private readonly ?string $secret,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function create(): TurnstileVerifierInterface
    {
        if (!$this->enabled) {
            return new AlwaysAllowTurnstileVerifier();
        }

        if ($this->secret === null || $this->secret === '') {
            throw new \RuntimeException(
                'Turnstile activé mais TURNSTILE_SECRET manquant. '
                .'Fournir le secret ou passer TURNSTILE_ENABLED=false.'
            );
        }

        return new CloudflareTurnstileVerifier(
            $this->secret,
            $this->httpClient,
            $this->logger,
        );
    }
}
