<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Security;

use App\Admin\Domain\AdminUser;
use App\Admin\Domain\AdminUserRepositoryInterface;
use App\Admin\Domain\Exception\AdminUserInvariantViolation;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * `UserProvider` custom : normalise l'identifiant (trim + lowercase) avant
 * la lecture. Empêche qu'un `Alice@Example.COM` échoue là où `alice@example.com`
 * réussirait — surface fréquente de bug côté form-login.
 *
 * Le format d'email est également validé à l'entrée : un identifiant hors
 * format ne peut pas correspondre à un enregistrement, mais on préfère
 * traiter le cas comme `UserNotFoundException` (message générique) plutôt que
 * comme une exception métier — le firewall n'a rien à savoir du format.
 *
 * @implements UserProviderInterface<AdminUser>
 */
final class AdminUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $repository,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $normalized = $this->normalize($identifier);

        $user = $normalized === null
            ? null
            : $this->repository->findByNormalizedEmail($normalized);

        if ($user === null) {
            throw new UserNotFoundException(\sprintf('Identifiant admin inconnu.'));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AdminUser) {
            throw new UnsupportedUserException(\sprintf(
                'Instances of "%s" are not supported.',
                $user::class,
            ));
        }

        $refreshed = $this->repository->findById($user->id());
        if ($refreshed === null) {
            throw new UserNotFoundException(\sprintf('Admin user "%s" is gone.', $user->getUserIdentifier()));
        }

        return $refreshed;
    }

    public function supportsClass(string $class): bool
    {
        return $class === AdminUser::class || is_subclass_of($class, AdminUser::class);
    }

    private function normalize(string $identifier): ?string
    {
        try {
            return \App\Admin\Domain\AdminEmail::fromString($identifier)->normalized();
        } catch (AdminUserInvariantViolation) {
            return null;
        }
    }
}
