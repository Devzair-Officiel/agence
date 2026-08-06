<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Security;

use App\Admin\Domain\AdminUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * `UserChecker` custom : refuse toute authentification pour un compte
 * `is_active = false`. Utilisé par le firewall `admin` (déclaré dans
 * `security.yaml`). Le message d'erreur reste générique (traité par Symfony
 * comme `BadCredentialsException` côté utilisateur) — pas de fuite indiquant
 * qu'un compte existe.
 */
final class AdminUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AdminUser) {
            return;
        }

        if (!$user->isActive()) {
            throw new DisabledException('Compte administrateur désactivé.');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        // Aucune vérification post-auth pour Phase 8C1 (pas d'expiration de mot
        // de passe ni de rotation forcée).
    }
}
