<?php

declare(strict_types=1);

namespace App\EditorialMedia\Presentation\Console;

use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Application\Storage\MediaVariantStorageInterface;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

/**
 * `bin/console editorial:media:cleanup-orphans [--dry-run]`
 *
 * Supprime les `MediaAsset` non référencés par aucun article (toutes statuts
 * confondus). Un asset orphelin est un asset dont l'UUID n'apparaît dans
 * aucune ligne de `editorial_article.hero_media_id`.
 *
 * Seuls les MediaAsset existants sont lus via le repository paginé.
 * La vérification d'orphelin se fait via une sous-requête DBAL légère.
 *
 * `--dry-run` : liste les orphelins sans les supprimer (toujours recommandé
 * avant la première suppression réelle).
 *
 * Codes de sortie :
 *   0 — succès (aucun orphelin ou tous supprimés) ;
 *   1 — au moins une erreur de suppression (les autres sont traités).
 */
#[AsCommand(
    name: 'editorial:media:cleanup-orphans',
    description: 'Supprime les MediaAsset non référencés par aucun article.',
)]
final class CleanupOrphanMediaCommand extends Command
{
    private const BATCH_SIZE = 50;

    public function __construct(
        private readonly MediaAssetRepositoryInterface $repository,
        private readonly MediaStorageInterface $storage,
        private readonly MediaVariantStorageInterface $variantStorage,
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Lister les orphelins sans les supprimer.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('Mode dry-run : aucune suppression ne sera effectuée.');
        }

        $totalAssets   = $this->repository->count();
        $page          = 1;
        $orphanCount   = 0;
        $errorCount    = 0;
        $deletedCount  = 0;

        $io->progressStart($totalAssets);

        while (true) {
            $batch = $this->repository->list($page, self::BATCH_SIZE);
            if ($batch === []) {
                break;
            }

            foreach ($batch as $asset) {
                $io->progressAdvance();

                if (!$this->isOrphan($asset)) {
                    continue;
                }

                ++$orphanCount;

                if ($dryRun) {
                    $io->text(\sprintf(
                        'ORPHELIN %s (créé %s)',
                        $asset->id()->toRfc4122(),
                        $asset->createdAt()->format('Y-m-d H:i'),
                    ));
                    continue;
                }

                $errors = $this->deleteAsset($asset);
                if ($errors !== []) {
                    ++$errorCount;
                    foreach ($errors as $err) {
                        $io->error($err);
                    }
                } else {
                    ++$deletedCount;
                }
            }

            ++$page;
            $this->entityManager->clear();
        }

        $io->progressFinish();

        if ($dryRun) {
            $io->success(\sprintf('%d orphelin(s) trouvé(s). Relancez sans --dry-run pour supprimer.', $orphanCount));

            return Command::SUCCESS;
        }

        if ($orphanCount === 0) {
            $io->success('Aucun orphelin trouvé.');

            return Command::SUCCESS;
        }

        $io->success(\sprintf('%d orphelin(s) supprimé(s).', $deletedCount));

        if ($errorCount > 0) {
            $io->warning(\sprintf('%d erreur(s) lors de la suppression — vérifier les logs.', $errorCount));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function isOrphan(MediaAsset $asset): bool
    {
        $result = $this->connection->fetchOne(
            'SELECT 1 FROM editorial_article WHERE hero_media_id = :id LIMIT 1',
            ['id' => $asset->id()->toRfc4122()],
        );

        return $result === false;
    }

    /**
     * Supprime les fichiers (original + variants) puis le row Doctrine.
     *
     * @return list<string> erreurs éventuelles (ne lève pas d'exception)
     */
    private function deleteAsset(MediaAsset $asset): array
    {
        $errors = [];
        $uuid   = $asset->id()->toRfc4122();

        foreach ([
            $asset->cardStorageKey(),
            $asset->heroStorageKey(),
        ] as $vk) {
            if ($vk === null) {
                continue;
            }
            try {
                $this->variantStorage->deleteVariant($vk);
            } catch (MediaStorageException $e) {
                $errors[] = \sprintf('Erreur variant %s/%s : %s', $uuid, $vk->toString(), $e->getMessage());
            }
        }

        try {
            $this->storage->delete($asset->storageKey());
        } catch (MediaStorageException $e) {
            $errors[] = \sprintf('Erreur original %s : %s', $uuid, $e->getMessage());
        }

        if ($errors !== []) {
            return $errors;
        }

        try {
            $managed = $this->entityManager->find(MediaAsset::class, $asset->id());
            if ($managed !== null) {
                $this->entityManager->remove($managed);
                $this->entityManager->flush();
            }
        } catch (\Throwable $e) {
            $errors[] = \sprintf('Erreur DB %s : %s', $uuid, $e->getMessage());
        }

        return $errors;
    }
}
