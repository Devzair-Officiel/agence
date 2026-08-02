<?php

declare(strict_types=1);

namespace App\Contact\Security;

/**
 * Implémentation par défaut en dev/test : autorise systématiquement.
 * Ne doit jamais être branchée en production (voir TurnstileVerifierFactory).
 */
final class AlwaysAllowTurnstileVerifier implements TurnstileVerifierInterface
{
    public function verify(?string $token, string $remoteIp): TurnstileVerdict
    {
        return TurnstileVerdict::ok();
    }
}
