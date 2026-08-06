<?php

declare(strict_types=1);

namespace App\Tests\Admin\Infrastructure;

use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Persistence\DoctrineAdminUserRepository;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Test d'intégration Doctrine — nécessite `devzair_test` accessible.
 * La table `admin_user` est purgée avant chaque test (order aléatoire).
 */
final class DoctrineAdminUserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private DoctrineAdminUserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = self::getContainer()->get(DoctrineAdminUserRepository::class);
        AdminDatabaseCleanup::purge($this->em);
    }

    public function testSaveAndFindByNormalizedEmail(): void
    {
        $user = AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('Alice@Example.COM'),
            'Alice',
            '$2y$04$abcdefghijklmnopqrstuu',
            new \DateTimeImmutable('2026-08-05T12:00:00+00:00'),
        );

        $this->repository->save($user);
        $this->em->clear();

        $found = $this->repository->findByNormalizedEmail('alice@example.com');
        self::assertNotNull($found);
        self::assertSame('Alice@Example.COM', $found->email()->display());
        self::assertSame('alice@example.com', $found->email()->normalized());
    }

    public function testFindByNormalizedEmailReturnsNullWhenUnknown(): void
    {
        self::assertNull($this->repository->findByNormalizedEmail('ghost@example.com'));
    }

    public function testFindById(): void
    {
        $id = Uuid::v7();
        $user = AdminUser::create(
            $id,
            AdminEmail::fromString('bob@example.com'),
            'Bob',
            '$2y$04$abcdefghijklmnopqrstuu',
            new \DateTimeImmutable('2026-08-05T12:00:00+00:00'),
        );
        $this->repository->save($user);
        $this->em->clear();

        $found = $this->repository->findById($id);
        self::assertNotNull($found);
        self::assertTrue($found->id()->equals($id));
    }

    public function testNormalizedEmailIsUnique(): void
    {
        $now = new \DateTimeImmutable('2026-08-05T12:00:00+00:00');
        $this->repository->save(AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('carol@example.com'),
            'Carol',
            '$2y$04$abcdefghijklmnopqrstuu',
            $now,
        ));

        $this->expectException(UniqueConstraintViolationException::class);
        $this->repository->save(AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('carol@example.com'),
            'Carol clone',
            '$2y$04$abcdefghijklmnopqrstuu',
            $now,
        ));
    }
}
