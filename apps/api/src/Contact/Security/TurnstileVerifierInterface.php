<?php

declare(strict_types=1);

namespace App\Contact\Security;

/**
 * Vérifie qu'un token Cloudflare Turnstile est valide.
 *
 * Contrat stable pour permettre :
 * - un fake en tests (AlwaysAllowTurnstileVerifier) ;
 * - un remplacement futur par hCaptcha, reCAPTCHA ou une solution interne
 *   sans toucher au domaine métier.
 */
interface TurnstileVerifierInterface
{
    public function verify(?string $token, string $remoteIp): TurnstileVerdict;
}
