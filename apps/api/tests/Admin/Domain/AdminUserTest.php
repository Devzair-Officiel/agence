<?php

declare(strict_types=1);

namespace App\Tests\Admin\Domain;

use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\AdminUser;
use App\Admin\Domain\Exception\AdminUserInvariantViolation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class AdminUserTest extends TestCase
{
    private const NOW = '2026-08-05T12:00:00+00:00';
    private const HASH = '$2y$13$abcdefghijklmnopqrstuu';

    public function testCreateStoresNormalizedEmailAndTrimsDisplayName(): void
    {
        $email = AdminEmail::fromString('Alice@Example.com');
        $user = AdminUser::create(
            Uuid::v7(),
            $email,
            '  Alice Smith  ',
            self::HASH,
            new \DateTimeImmutable(self::NOW),
        );

        self::assertSame('Alice Smith', $user->displayName());
        self::assertSame('alice@example.com', $user->email()->normalized());
        self::assertSame('Alice@Example.com', $user->email()->display());
        self::assertSame('alice@example.com', $user->getUserIdentifier());
        self::assertSame(['ROLE_ADMIN'], $user->getRoles());
        self::assertSame(self::HASH, $user->getPassword());
        self::assertTrue($user->isActive());
        self::assertNull($user->lastLoginAt());
        self::assertEquals(new \DateTimeImmutable(self::NOW), $user->createdAt());
        self::assertEquals($user->createdAt(), $user->updatedAt());
    }

    public function testRejectsEmptyDisplayName(): void
    {
        $this->expectException(AdminUserInvariantViolation::class);
        AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('a@b.io'),
            '   ',
            self::HASH,
            new \DateTimeImmutable(self::NOW),
        );
    }

    public function testRejectsOverlongDisplayName(): void
    {
        $this->expectException(AdminUserInvariantViolation::class);
        AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('a@b.io'),
            str_repeat('n', 121),
            self::HASH,
            new \DateTimeImmutable(self::NOW),
        );
    }

    public function testRejectsEmptyPasswordHash(): void
    {
        $this->expectException(AdminUserInvariantViolation::class);
        AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('a@b.io'),
            'Alice',
            '   ',
            new \DateTimeImmutable(self::NOW),
        );
    }

    public function testChangePasswordHashBumpsUpdatedAt(): void
    {
        $user = $this->makeUser();
        $later = new \DateTimeImmutable('2026-08-05T13:00:00+00:00');

        $user->changePasswordHash('$2y$13$newnewnewnewnewnewnewnw', $later);

        self::assertSame('$2y$13$newnewnewnewnewnewnewnw', $user->getPassword());
        self::assertEquals($later, $user->updatedAt());
    }

    public function testDeactivateIsIdempotent(): void
    {
        $user = $this->makeUser();
        $t1 = new \DateTimeImmutable('2026-08-05T13:00:00+00:00');
        $t2 = new \DateTimeImmutable('2026-08-05T14:00:00+00:00');

        $user->deactivate($t1);
        self::assertFalse($user->isActive());
        self::assertEquals($t1, $user->updatedAt());

        $user->deactivate($t2);
        // Deuxième désactivation ne doit pas rebumper updated_at.
        self::assertEquals($t1, $user->updatedAt());
    }

    public function testActivateIsIdempotent(): void
    {
        $user = $this->makeUser();
        $t1 = new \DateTimeImmutable('2026-08-05T13:00:00+00:00');
        $user->deactivate($t1);

        $t2 = new \DateTimeImmutable('2026-08-05T14:00:00+00:00');
        $user->activate($t2);
        self::assertTrue($user->isActive());
        self::assertEquals($t2, $user->updatedAt());

        $t3 = new \DateTimeImmutable('2026-08-05T15:00:00+00:00');
        $user->activate($t3);
        self::assertEquals($t2, $user->updatedAt());
    }

    public function testRecordSuccessfulLoginSetsLastLoginAt(): void
    {
        $user = $this->makeUser();
        $loginAt = new \DateTimeImmutable('2026-08-05T14:30:00+00:00');

        $user->recordSuccessfulLogin($loginAt);

        self::assertEquals($loginAt, $user->lastLoginAt());
        self::assertEquals($loginAt, $user->updatedAt());
    }

    public function testEraseCredentialsIsANoOp(): void
    {
        $user = $this->makeUser();
        $before = $user->getPassword();
        $user->eraseCredentials();
        self::assertSame($before, $user->getPassword());
    }

    private function makeUser(): AdminUser
    {
        return AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('alice@example.com'),
            'Alice',
            self::HASH,
            new \DateTimeImmutable(self::NOW),
        );
    }
}
