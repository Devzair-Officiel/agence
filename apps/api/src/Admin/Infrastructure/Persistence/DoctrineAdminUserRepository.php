<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Persistence;

use App\Admin\Domain\AdminUser;
use App\Admin\Domain\AdminUserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Uid\Uuid;

/**
 * Adaptateur Doctrine du port `AdminUserRepositoryInterface`.
 *
 * `save()` fait `persist` + `flush` explicite : la Phase 8C1 n'a pas de
 * gestionnaire de transaction dédié, les cas d'usage sont indépendants et
 * cette simplicité évite tout couplage caché à un middleware transactionnel.
 */
final class DoctrineAdminUserRepository implements AdminUserRepositoryInterface
{
    /**
     * @var EntityRepository<AdminUser>
     */
    private readonly EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $entityManager->getRepository(AdminUser::class);
    }

    public function save(AdminUser $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function findByNormalizedEmail(string $normalizedEmail): ?AdminUser
    {
        return $this->repository->findOneBy(['normalizedEmail' => $normalizedEmail]);
    }

    public function findById(Uuid $id): ?AdminUser
    {
        return $this->repository->find($id);
    }
}
