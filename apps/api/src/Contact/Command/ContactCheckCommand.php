<?php

declare(strict_types=1);

namespace App\Contact\Command;

use App\Contact\Configuration\ContactConfigurationIssue;
use App\Contact\Configuration\ContactConfigurationValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande Ops de diagnostic : `bin/console app:contact:check`.
 *
 * N'envoie *aucun* email, n'appelle *aucun* service distant. Se contente
 * d'inspecter la configuration en mémoire (env vars déjà résolues par le
 * container) et d'afficher les incohérences détectées par
 * {@see ContactConfigurationValidator}.
 *
 * Codes de sortie :
 * - 0 : aucune erreur (les warnings n'échouent pas la commande) ;
 * - 1 : au moins une erreur détectée → à traiter avant déploiement.
 *
 * La sortie ne révèle jamais le DSN complet, le secret Turnstile, ni les
 * adresses email complètes : safe à coller dans un ticket d'ops.
 */
#[AsCommand(
    name: 'app:contact:check',
    description: 'Vérifie la configuration du pipeline de contact sans effectuer d\'envoi réel.',
)]
final class ContactCheckCommand extends Command
{
    public function __construct(
        private readonly ContactConfigurationValidator $validator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Devzair — Diagnostic du pipeline de contact');

        $report = $this->validator->validate();
        $errors = $report->errors();
        $warnings = $report->warnings();

        if ($errors === [] && $warnings === []) {
            $io->success('Configuration OK : aucune anomalie détectée.');

            return Command::SUCCESS;
        }

        if ($errors !== []) {
            $io->section(sprintf('%d erreur(s) bloquante(s)', \count($errors)));
            $io->table(
                ['Champ', 'Code', 'Message'],
                array_map(fn (ContactConfigurationIssue $i) => [$i->field, $i->code, $i->message], $errors),
            );
        }

        if ($warnings !== []) {
            $io->section(sprintf('%d avertissement(s)', \count($warnings)));
            $io->table(
                ['Champ', 'Code', 'Message'],
                array_map(fn (ContactConfigurationIssue $i) => [$i->field, $i->code, $i->message], $warnings),
            );
        }

        if ($errors !== []) {
            $io->error('Configuration invalide : corrigez les erreurs ci-dessus avant déploiement.');

            return Command::FAILURE;
        }

        $io->warning('Configuration acceptable mais des avertissements sont présents.');

        return Command::SUCCESS;
    }
}
