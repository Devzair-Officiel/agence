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
 * Réinitialise le mot de passe d'un administrateur existant. Ne renvoie
 * jamais aucun mot de passe : la nouvelle valeur doit être communiquée hors
 * bande (canal sécurisé) à l'intéressé.
 */
#[AsCommand(
    name: 'app:admin:reset-password',
    description: 'Réinitialise le mot de passe d\'un administrateur existant.',
)]
final class ResetAdminPasswordCommand extends Command
{
    public function __construct(
        private readonly AdminAccountService $accountService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Adresse email du compte à réinitialiser.')
            ->addOption('password-stdin', null, InputOption::VALUE_NONE, 'Lit le nouveau mot de passe sur STDIN.')
        ;
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

        $password = $input->getOption('password-stdin')
            ? self::readStdinLine()
            : (string) $io->askHidden('Nouveau mot de passe (min. 12 caractères, saisie masquée)');

        try {
            $this->accountService->resetPassword($email, $password);
        } catch (AdminUserInvariantViolation | AdminUserNotFoundException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('Mot de passe réinitialisé.');

        return Command::SUCCESS;
    }

    private static function readStdinLine(): string
    {
        $handle = fopen('php://stdin', 'rb');
        if ($handle === false) {
            return '';
        }
        $line = fgets($handle);
        fclose($handle);
        if ($line === false) {
            return '';
        }

        return rtrim($line, "\r\n");
    }
}
