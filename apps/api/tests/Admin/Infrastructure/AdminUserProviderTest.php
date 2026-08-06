<?php

declare(strict_types=1);

namespace App\Tests\Admin\Infrastructure;

use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Security\AdminUserProvider;
use App\Tests\Admin\Support\InMemoryAdminUserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Uid\Uuid;

final class AdminUserProviderTest extends TestCase
{
    private InMemoryAdminUserRepository $repository;
    private AdminUserProvider $provider;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAdminUserRepository();
        $this->provider = new AdminUserProvider($this->repository);
    }

    public function testLoadByIdentifierNormalizesEmail(): void
    {
        $this->seedAlice();

        $user = $this->provider->loadUserByIdentifier('  Alice@Example.COM  ');
        self::assertInstanceOf(AdminUser::class, $user);
        self::assertSame('alice@example.com', $user->getUserIdentifier());
    }

    public function testLoadByIdentifierThrowsWhenUnknown(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->provider->loadUserByIdentifier('ghost@example.com');
    }

    public function testLoadByIdentifierThrowsWhenIdentifierMalformed(): void
    {
        $this->expectException(UserNotFoundException::class);
        // Format invalide : le provider retombe sur UserNotFoundException plutôt
        // que de laisser fuiter une AdminUserInvariantViolation.
        $this->provider->loadUserByIdentifier('not-an-email');
    }

    public function testRefreshUserReloadsFromRepository(): void
    {
        $alice = $this->seedAlice();

        $refreshed = $this->provider->refreshUser($alice);
        self::assertInstanceOf(AdminUser::class, $refreshed);
        self::assertSame($alice->id()->toRfc4122(), $refreshed->id()->toRfc4122());
    }

    public function testRefreshUserThrowsForUnsupportedType(): void
    {
        $this->expectException(UnsupportedUserException::class);
        $this->provider->refreshUser(new InMemoryUser('foo', null));
    }

    public function testSupportsClass(): void
    {
        self::assertTrue($this->provider->supportsClass(AdminUser::class));
        self::assertFalse($this->provider->supportsClass(InMemoryUser::class));
    }

    private function seedAlice(): AdminUser
    {
        $alice = AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('Alice@Example.com'),
            'Alice',
            '$2y$04$abcdefghijklmnopqrstuu',
            new \DateTimeImmutable('2026-08-05T12:00:00+00:00'),
        );
        $this->repository->save($alice);

        return $alice;
    }
}
