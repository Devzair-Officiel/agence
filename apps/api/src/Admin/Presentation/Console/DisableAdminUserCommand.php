<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Console;

use App\Admin\Application\AdminAccountService;
use App\Admin\Domain\Exception\AdminUserInvariantViolation;
use App\Admin\Domain\Exception\AdminUserNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Désactive un administrateur. Le compte reste en base (audit / réactivation
 * possible plus tard) mais toute tentative de login est refusée par
 * `AdminUserChecker`. Idempotent : re-désactiver un compte déjà désactivé
 * ne renvoie pas d'erreur.
 */
#[AsCommand(
    name: 'app:admin:disable',
    description: 'Désactive un administrateur (empêche toute authentification ultérieure).',
)]
final class DisableAdminUserCommand extends Command
{
    public function __construct(
        private readonly AdminAccountService $accountService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('email', null, InputOption::VALUE_REQUIRED, 'Adresse email du compte à désactiver.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $emailOption = $input->getOption('email');
        $email = \is_string($emailOption) && $emailOption !== ''
            ? $emailOption
            : (string) $io->ask('Email');

        if ($email === '') {
            $io->error('Un email est requis.');

            return Command::FAILURE;
        }

        try {
            $this->accountService->disableAdmin($email);
        } catch (AdminUserInvariantViolation | AdminUserNotFoundException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('Administrateur désactivé.');

        return Command::SUCCESS;
    }
}
