<?php

declare(strict_types=1);

namespace App\Admin\Application;

use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\AdminUser;
use App\Admin\Domain\AdminUserRepositoryInterface;
use App\Admin\Domain\Exception\AdminUserAlreadyExistsException;
use App\Admin\Domain\Exception\AdminUserNotFoundException;
use Psr\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Orchestration des cas d'usage administrateurs :
 * - créer un compte administrateur ;
 * - réinitialiser un mot de passe existant ;
 * - désactiver / réactiver un compte.
 *
 * Aucune I/O HTTP ici : ce service est appelé exclusivement depuis les
 * commandes CLI (Phase 8C1) et sera réutilisé par un formulaire admin plus
 * tard sans être modifié.
 */
final class AdminAccountService
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $repository,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
        private readonly ClockInterface $clock,
    ) {
    }

    public function createAdmin(string $email, string $displayName, string $plainPassword): AdminUser
    {
        $adminEmail = AdminEmail::fromString($email);
        PasswordPolicy::assert($plainPassword, $adminEmail);

        if ($this->repository->findByNormalizedEmail($adminEmail->normalized()) !== null) {
            throw AdminUserAlreadyExistsException::withEmail($adminEmail->normalized());
        }

        $hash = $this->hasherFactory
            ->getPasswordHasher(AdminUser::class)
            ->hash($plainPassword);

        $now = \DateTimeImmutable::createFromInterface($this->clock->now());
        $user = AdminUser::create(Uuid::v7(), $adminEmail, $displayName, $hash, $now);
        $this->repository->save($user);

        return $user;
    }

    public function resetPassword(string $email, string $newPlainPassword): AdminUser
    {
        $adminEmail = AdminEmail::fromString($email);
        PasswordPolicy::assert($newPlainPassword, $adminEmail);

        $user = $this->repository->findByNormalizedEmail($adminEmail->normalized());
        if ($user === null) {
            throw AdminUserNotFoundException::byEmail($adminEmail->normalized());
        }

        $hash = $this->hasherFactory
            ->getPasswordHasher(AdminUser::class)
            ->hash($newPlainPassword);

        $user->changePasswordHash($hash, \DateTimeImmutable::createFromInterface($this->clock->now()));
        $this->repository->save($user);

        return $user;
    }

    public function disableAdmin(string $email): AdminUser
    {
        $adminEmail = AdminEmail::fromString($email);
        $user = $this->repository->findByNormalizedEmail($adminEmail->normalized());
        if ($user === null) {
            throw AdminUserNotFoundException::byEmail($adminEmail->normalized());
        }

        $user->deactivate(\DateTimeImmutable::createFromInterface($this->clock->now()));
        $this->repository->save($user);

        return $user;
    }
}
