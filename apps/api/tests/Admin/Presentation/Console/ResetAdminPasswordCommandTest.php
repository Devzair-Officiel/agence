<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Console;

use App\Admin\Application\AdminAccountService;
use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\AdminUser;
use App\Admin\Presentation\Console\ResetAdminPasswordCommand;
use App\Tests\Admin\Support\InMemoryAdminUserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Uid\Uuid;

final class ResetAdminPasswordCommandTest extends TestCase
{
    private InMemoryAdminUserRepository $repository;
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAdminUserRepository();
        $hasherFactory = new PasswordHasherFactory([
            AdminUser::class => ['algorithm' => 'bcrypt', 'cost' => 4],
        ]);
        $service = new AdminAccountService(
            $this->repository,
            $hasherFactory,
            new MockClock('2026-08-05T12:00:00+00:00'),
        );
        $this->tester = new CommandTester(new ResetAdminPasswordCommand($service));
    }

    public function testResetsExistingUserPassword(): void
    {
        $this->seedAlice('$2y$04$oldoldoldoldoldoldoldoo');
        $this->tester->setInputs(['NewCorrectHorse!2026']);

        $status = $this->tester->execute(['--email' => 'alice@example.com']);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('réinitialisé', $this->tester->getDisplay());
        $user = $this->repository->findByNormalizedEmail('alice@example.com');
        self::assertNotNull($user);
        self::assertNotSame('$2y$04$oldoldoldoldoldoldoldoo', $user->getPassword());
    }

    public function testFailsWhenUserUnknown(): void
    {
        $this->tester->setInputs(['ghost@example.com', 'NewCorrectHorse!2026']);

        $status = $this->tester->execute([]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('Aucun administrateur', $this->tester->getDisplay());
    }

    public function testFailsWhenPasswordShort(): void
    {
        $this->seedAlice('$2y$04$oldoldoldoldoldoldoldoo');
        $this->tester->setInputs(['short']);

        $status = $this->tester->execute(['--email' => 'alice@example.com']);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('12 caractères', $this->tester->getDisplay());
    }

    private function seedAlice(string $hash): void
    {
        $this->repository->save(AdminUser::create(
            Uuid::v7(),
            AdminEmail::fromString('alice@example.com'),
            'Alice',
            $hash,
            new \DateTimeImmutable('2026-08-05T12:00:00+00:00'),
        ));
    }
}
