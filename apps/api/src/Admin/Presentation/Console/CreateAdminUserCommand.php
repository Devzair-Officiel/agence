<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Console;

use App\Admin\Application\AdminAccountService;
use App\Admin\Domain\Exception\AdminUserAlreadyExistsException;
use App\Admin\Domain\Exception\AdminUserInvariantViolation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Crée un administrateur dans la base — commande utilisée pour le premier
 * bootstrap et pour ajouter un compte supplémentaire.
 *
 * Deux modes d'entrée du mot de passe :
 * - interactif : `askHidden()` (n'apparaît ni dans le TTY ni dans les logs) ;
 * - automatisable : `--password-stdin` (lit une ligne unique depuis STDIN).
 *
 * Le mot de passe ne peut jamais être passé par argument CLI ni par option
 * `--password=xxx` : il resterait dans l'historique shell et dans le
 * process listing.
 *
 * La commande N'EST PAS scopée par `#[When]` — elle doit rester disponible
 * en production pour permettre la création du premier admin après
 * déploiement (voir docs/checklists à créer).
 */
#[AsCommand(
    name: 'app:admin:create-user',
    description: 'Crée un compte administrateur (interactif ou --password-stdin).',
)]
final class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly AdminAccountService $accountService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Adresse email de l\'administrateur.')
            ->addOption('display-name', null, InputOption::VALUE_REQUIRED, 'Nom affiché dans le dashboard.')
            ->addOption('password-stdin', null, InputOption::VALUE_NONE, 'Lit le mot de passe sur STDIN (une seule ligne).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = self::stringOption($input, 'email') ?? $io->ask('Email');
        $displayName = self::stringOption($input, 'display-name') ?? $io->ask('Nom affiché');

        if (!\is_string($email) || $email === '') {
            $io->error('Un email est requis.');

            return Command::FAILURE;
        }
        if (!\is_string($displayName) || $displayName === '') {
            $io->error('Un nom affiché est requis.');

            return Command::FAILURE;
        }

        $password = $input->getOption('password-stdin')
            ? self::readStdinLine()
            : (string) $io->askHidden('Mot de passe (min. 12 caractères, saisie masquée)');

        try {
            $user = $this->accountService->createAdmin($email, $displayName, $password);
        } catch (AdminUserInvariantViolation | AdminUserAlreadyExistsException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(\sprintf(
            'Administrateur créé (id=%s, email=%s).',
            (string) $user->id(),
            $user->email()->display(),
        ));

        return Command::SUCCESS;
    }

    private static function stringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);
        if ($value === null) {
            return null;
        }

        return \is_string($value) ? $value : null;
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
