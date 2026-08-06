<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Console;

use App\Admin\Application\AdminAccountService;
use App\Admin\Domain\AdminUser;
use App\Admin\Presentation\Console\CreateAdminUserCommand;
use App\Tests\Admin\Support\InMemoryAdminUserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

/**
 * Tests unitaires de la commande. Le mode `--password-stdin` (fopen php://stdin)
 * n'est pas testable ici — CommandTester n'injecte pas dans le vrai flux stdin.
 * Il est validé par le smoke-test CLI intégral en Phase 8C1.
 */
final class CreateAdminUserCommandTest extends TestCase
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
        $command = new CreateAdminUserCommand($service);
        $this->tester = new CommandTester($command);
    }

    public function testCreatesAdminInInteractiveMode(): void
    {
        $this->tester->setInputs(['alice@example.com', 'Alice Smith', 'CorrectHorseBattery!']);

        $status = $this->tester->execute([]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('Administrateur créé', $this->tester->getDisplay());
        self::assertNotNull($this->repository->findByNormalizedEmail('alice@example.com'));
    }

    public function testUsesOptionsWhenProvided(): void
    {
        $this->tester->setInputs(['CorrectHorseBattery!']);

        $status = $this->tester->execute([
            '--email' => 'Alice@Example.com',
            '--display-name' => 'Alice',
        ]);

        self::assertSame(Command::SUCCESS, $status);
        $user = $this->repository->findByNormalizedEmail('alice@example.com');
        self::assertNotNull($user);
        self::assertSame('Alice@Example.com', $user->email()->display());
    }

    public function testRejectsDuplicate(): void
    {
        $this->tester->setInputs(['CorrectHorseBattery!']);
        $this->tester->execute([
            '--email' => 'alice@example.com',
            '--display-name' => 'Alice',
        ]);

        $tester2 = clone $this->tester;
        $tester2->setInputs(['AnotherStrongPass1!']);
        $status = $tester2->execute([
            '--email' => 'Alice@example.com',
            '--display-name' => 'Alice bis',
        ]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('existe déjà', $tester2->getDisplay());
    }

    public function testRejectsShortPassword(): void
    {
        $this->tester->setInputs(['tooshort']);

        $status = $this->tester->execute([
            '--email' => 'alice@example.com',
            '--display-name' => 'Alice',
        ]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('12 caractères', $this->tester->getDisplay());
    }

    public function testDoesNotAcceptPasswordAsCliOption(): void
    {
        $definition = (new CreateAdminUserCommand(
            new AdminAccountService(
                new InMemoryAdminUserRepository(),
                new PasswordHasherFactory([AdminUser::class => ['algorithm' => 'bcrypt', 'cost' => 4]]),
                new MockClock('2026-08-05T12:00:00+00:00'),
            ),
        ))->getDefinition();

        // Le mot de passe ne doit JAMAIS être passable via --password=xxx :
        // il resterait dans le process list / historique shell.
        self::assertFalse($definition->hasOption('password'));
    }
}
