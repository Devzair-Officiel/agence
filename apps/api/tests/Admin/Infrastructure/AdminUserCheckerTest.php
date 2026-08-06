<?php

declare(strict_types=1);

namespace App\Tests\Admin\Infrastructure;

use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Security\AdminUserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Uid\Uuid;

final class AdminUserCheckerTest extends TestCase
{
    public function testAcceptsActiveAdmin(): void
    {
        $checker = new AdminUserChecker();
        $user = $this->makeAdmin(active: true);

        $checker->checkPreAuth($user);
        $checker->checkPostAuth($user);
        $this->addToAssertionCount(1);
    }

    public function testRejectsDisabledAdmin(): void
    {
        $checker = new AdminUserChecker();
        $user = $this->makeAdmin(active: false);

        $this->expectException(DisabledException::class);
        $checker->checkPreAuth($user);
    }

    public function testIgnoresNonAdminUsers(): void
    {
        $checker = new AdminUserChecker();
        $checker->checkPreAuth(new InMemoryUser('foo', null, ['ROLE_USER'], false));
        // Ne devrait pas lever DisabledException même si le compte est marqué
        // non enabled : le checker ne gère QUE des AdminUser.
        $this->addToAssertionCount(1);
    }

    private function makeAdmin(bool $active): AdminUser
    {
        $user = AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('alice@example.com'),
            'Alice',
            '$2y$04$abcdefghijklmnopqrstuu',
            new \DateTimeImmutable('2026-08-05T12:00:00+00:00'),
        );
        if (!$active) {
            $user->deactivate(new \DateTimeImmutable('2026-08-05T12:01:00+00:00'));
        }

        return $user;
    }
}
