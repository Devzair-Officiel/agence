<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Console;

use App\Admin\Application\AdminAccountService;
use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\AdminUser;
use App\Admin\Presentation\Console\DisableAdminUserCommand;
use App\Tests\Admin\Support\InMemoryAdminUserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Uid\Uuid;

final class DisableAdminUserCommandTest extends TestCase
{
    private InMemoryAdminUserRepository $repository;
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAdminUserRepository();
        $service = new AdminAccountService(
            $this->repository,
            new PasswordHasherFactory([AdminUser::class => ['algorithm' => 'bcrypt', 'cost' => 4]]),
            new MockClock('2026-08-05T12:00:00+00:00'),
        );
        $this->tester = new CommandTester(new DisableAdminUserCommand($service));
    }

    public function testDisablesExistingAdmin(): void
    {
        $this->repository->save(AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('alice@example.com'),
            'Alice',
            '$2y$04$abcdefghijklmnopqrstuu',
            new \DateTimeImmutable('2026-08-05T12:00:00+00:00'),
        ));

        $status = $this->tester->execute(['--email' => 'Alice@Example.com']);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('désactivé', $this->tester->getDisplay());
        $user = $this->repository->findByNormalizedEmail('alice@example.com');
        self::assertNotNull($user);
        self::assertFalse($user->isActive());
    }

    public function testFailsForUnknownAdmin(): void
    {
        $status = $this->tester->execute(['--email' => 'ghost@example.com']);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('Aucun administrateur', $this->tester->getDisplay());
    }
}
