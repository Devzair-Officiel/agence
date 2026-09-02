<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Console;

use App\Estimator\Domain\Lead\EstimatorLeadRepositoryInterface;
use App\Estimator\Domain\Partnership\EstimatorPartnershipRepositoryInterface;
use App\Estimator\Domain\Retention\EstimatorRetentionPolicy;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Purge des données estimateur expirées conformément à la politique de rétention V1.
 *
 * Politique : EstimatorRetentionPolicy::MONTHS (24 mois depuis createdAt).
 * Règle de frontière : createdAt < cutoff → expiré (borne strictement inférieure).
 *
 * Ordre de suppression :
 *   1. Propositions de partenariat (peuvent référencer un lead via lead_request_id)
 *   2. Leads estimateur
 *
 * La relation lead_request_id est un VARCHAR sans FK Doctrine (DEST-007 / EST-7) :
 * aucune contrainte DB n'est violée. Une proposition non expirée peut conserver
 * une référence vers un lead déjà purgé — son snapshot JSONB contient les données
 * nécessaires au traitement commercial.
 *
 * Sortie : ne jamais afficher PII (nom, email, téléphone, société, texte projet).
 *
 * Codes de sortie :
 *   0 = succès ou dry-run
 *   1 = erreur inattendue (exception)
 *
 * Fréquence recommandée : 1 exécution quotidienne.
 * Automatisation : ajouter dans le crontab du serveur ou l'infrastructure CI/CD.
 * Exemple cron : 0 3 * * * docker compose exec api php bin/console app:estimator:purge-expired
 */
#[AsCommand(
    name: 'app:estimator:purge-expired',
    description: 'Supprime les leads et propositions de partenariat estimateur expirés (> 24 mois).',
)]
final class PurgeExpiredEstimatorDataCommand extends Command
{
    public function __construct(
        private readonly EstimatorLeadRepositoryInterface $leadRepository,
        private readonly EstimatorPartnershipRepositoryInterface $partnershipRepository,
        private readonly EntityManagerInterface $em,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $estimatorLeadLogger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Affiche le nombre d\'entrées expirées sans effectuer de suppression.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $cutoff = EstimatorRetentionPolicy::cutoff($this->clock->now());

        $io->title('Estimateur — Purge des données expirées');
        $io->writeln(\sprintf(
            'Politique : %d mois | Seuil : %s | Mode : %s',
            EstimatorRetentionPolicy::MONTHS,
            $cutoff->format('Y-m-d H:i:s'),
            $dryRun ? 'DRY-RUN (aucune suppression)' : 'PURGE RÉELLE',
        ));

        try {
            if ($dryRun) {
                $leadCount        = $this->leadRepository->countExpiredBefore($cutoff);
                $partnershipCount = $this->partnershipRepository->countExpiredBefore($cutoff);

                $io->table(
                    ['Type', 'Expirés (à supprimer)'],
                    [
                        ['Leads estimateur', $leadCount],
                        ['Propositions de partenariat', $partnershipCount],
                    ],
                );

                $this->estimatorLeadLogger->info('estimator_retention_purge_dry_run', [
                    'cutoff'            => $cutoff->format(\DateTimeInterface::ATOM),
                    'lead_count'        => $leadCount,
                    'partnership_count' => $partnershipCount,
                ]);

                $io->success('Dry-run terminé. Aucune donnée supprimée.');

                return Command::SUCCESS;
            }

            $this->em->beginTransaction();
            try {
                // Propositions d'abord : elles peuvent référencer un lead
                $partnershipCount = $this->partnershipRepository->deleteExpiredBefore($cutoff);
                $leadCount        = $this->leadRepository->deleteExpiredBefore($cutoff);
                $this->em->commit();
            } catch (\Throwable $e) {
                $this->em->rollback();
                throw $e;
            }

            $this->estimatorLeadLogger->info('estimator_retention_purge_completed', [
                'cutoff'            => $cutoff->format(\DateTimeInterface::ATOM),
                'lead_count'        => $leadCount,
                'partnership_count' => $partnershipCount,
            ]);

            $io->success(\sprintf(
                '%d lead(s) supprimé(s). %d proposition(s) de partenariat supprimée(s).',
                $leadCount,
                $partnershipCount,
            ));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error(\sprintf('Erreur inattendue : %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
