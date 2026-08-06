<?php

declare(strict_types=1);

namespace App\Tests\Admin\Application;

use App\Admin\Application\AdminAccountService;
use App\Admin\Domain\AdminUser;
use App\Admin\Domain\Exception\AdminUserAlreadyExistsException;
use App\Admin\Domain\Exception\AdminUserInvariantViolation;
use App\Admin\Domain\Exception\AdminUserNotFoundException;
use App\Tests\Admin\Support\InMemoryAdminUserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

final class AdminAccountServiceTest extends TestCase
{
    private InMemoryAdminUserRepository $repository;
    private MockClock $clock;
    private AdminAccountService $service;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAdminUserRepository();
        $this->clock = new MockClock('2026-08-05T12:00:00+00:00');
        // Bcrypt cost 4 : couverture réaliste du hash sans coût mémoire Argon2id.
        $hasherFactory = new PasswordHasherFactory([
            AdminUser::class => ['algorithm' => 'bcrypt', 'cost' => 4],
        ]);
        $this->service = new AdminAccountService($this->repository, $hasherFactory, $this->clock);
    }

    public function testCreateAdminPersistsHashedPassword(): void
    {
        $user = $this->service->createAdmin('Alice@Example.com', 'Alice', 'CorrectHorseBattery!');

        self::assertSame('alice@example.com', $user->email()->normalized());
        self::assertNotSame('CorrectHorseBattery!', $user->getPassword());
        self::assertMatchesRegularExpression('/^\$2y\$04\$/', $user->getPassword());
        self::assertSame(1, $this->repository->count());
    }

    public function testCreateRejectsDuplicateEmailIgnoringCase(): void
    {
        $this->service->createAdmin('Alice@Example.com', 'Alice', 'CorrectHorseBattery!');

        $this->expectException(AdminUserAlreadyExistsException::class);
        $this->service->createAdmin('ALICE@example.COM', 'Alice2', 'AnotherStrongPass!');
    }

    public function testCreateRejectsShortPassword(): void
    {
        $this->expectException(AdminUserInvariantViolation::class);
        $this->service->createAdmin('alice@example.com', 'Alice', 'short');
    }

    public function testResetPasswordUpdatesHashOnly(): void
    {
        $user = $this->service->createAdmin('alice@example.com', 'Alice', 'CorrectHorseBattery!');
        $originalHash = $user->getPassword();

        $this->clock->modify('+1 hour');
        $refreshed = $this->service->resetPassword('alice@example.com', 'ADifferentPass123!');

        self::assertNotSame($originalHash, $refreshed->getPassword());
        self::assertSame($user->id()->toRfc4122(), $refreshed->id()->toRfc4122());
    }

    public function testResetPasswordFailsWhenUserUnknown(): void
    {
        $this->expectException(AdminUserNotFoundException::class);
        $this->service->resetPassword('ghost@example.com', 'CorrectHorseBattery!');
    }

    public function testDisableAdminFlipsIsActive(): void
    {
        $this->service->createAdmin('alice@example.com', 'Alice', 'CorrectHorseBattery!');

        $user = $this->service->disableAdmin('Alice@Example.com');

        self::assertFalse($user->isActive());
    }

    public function testDisableAdminFailsWhenUserUnknown(): void
    {
        $this->expectException(AdminUserNotFoundException::class);
        $this->service->disableAdmin('ghost@example.com');
    }
}
