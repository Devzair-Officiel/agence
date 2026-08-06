<?php

declare(strict_types=1);

namespace App\Admin\Domain;

use Symfony\Component\Uid\Uuid;

/**
 * Port du domaine Admin. L'implémentation Doctrine vit dans
 * `App\Admin\Infrastructure\Persistence\DoctrineAdminUserRepository`.
 *
 * Les recherches par email s'appuient toujours sur la forme normalisée
 * (lowercase + trim). Aucun consommateur ne doit reformer l'email lui-même.
 */
interface AdminUserRepositoryInterface
{
    public function save(AdminUser $user): void;

    public function findByNormalizedEmail(string $normalizedEmail): ?AdminUser;

    public function findById(Uuid $id): ?AdminUser;
}
