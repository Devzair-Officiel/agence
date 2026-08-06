<?php

declare(strict_types=1);

namespace App\Tests\Admin\Support;

use App\Admin\Domain\AdminUser;
use App\Admin\Domain\AdminUserRepositoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Repository en mémoire pour les tests Application/Domain — évite de
 * bootstrapper Doctrine quand on teste des règles métier pures.
 */
final class InMemoryAdminUserRepository implements AdminUserRepositoryInterface
{
    /** @var array<string, AdminUser> */
    private array $byId = [];

    /** @var array<string, string> */
    private array $idsByEmail = [];

    public function save(AdminUser $user): void
    {
        $id = (string) $user->id();
        $this->byId[$id] = $user;
        $this->idsByEmail[$user->email()->normalized()] = $id;
    }

    public function findByNormalizedEmail(string $normalizedEmail): ?AdminUser
    {
        $id = $this->idsByEmail[$normalizedEmail] ?? null;

        return $id === null ? null : ($this->byId[$id] ?? null);
    }

    public function findById(Uuid $id): ?AdminUser
    {
        return $this->byId[(string) $id] ?? null;
    }

    public function count(): int
    {
        return \count($this->byId);
    }
}
