<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Presentation\Console;

use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Presentation\Console\CleanupOrphanMediaCommand;
use App\Tests\EditorialMedia\Support\FakeMediaStorage;
use App\Tests\EditorialMedia\Support\InMemoryMediaAssetRepository;
use App\Tests\EditorialMedia\Support\MediaAssetBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Uuid;

/**
 * Tests unitaires de CleanupOrphanMediaCommand (snapshot vs OFFSET).
 *
 * Le scénario critique utilise 60 assets (> BATCH_SIZE=50). Avec l'ancienne
 * approche paginée OFFSET, après la suppression du premier lot (50 items),
 * il ne restait que 10 items en base : `list(page=2, perPage=50)` avec
 * OFFSET=50 retournait 0 résultats et 10 orphelins étaient silencieusement
 * ignorés. Le snapshot capture les 60 UUIDs AVANT toute suppression.
 */
final class CleanupOrphanMediaCommandTest extends TestCase
{
    private function makeCommand(
        InMemoryMediaAssetRepository $repo,
        FakeMediaStorage $storage,
        Connection $connection,
        EntityManagerInterface $em,
    ): CleanupOrphanMediaCommand {
        return new CleanupOrphanMediaCommand(
            repository: $repo,
            storage: $storage,
            variantStorage: $storage,
            entityManager: $em,
            connection: $connection,
        );
    }

    /**
     * Crée un mock Connection dont `fetchOne()` renvoie `false` (orphelin)
     * pour les $orphanIds et `1` (référencé) pour les autres.
     *
     * @param list<string> $orphanIds UUIDs considérés orphelins (rfc4122)
     */
    private function connectionMock(array $orphanIds): Connection
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchOne')->willReturnCallback(
            static function (string $sql, array $params) use ($orphanIds): mixed {
                return \in_array($params['id'], $orphanIds, true) ? false : 1;
            },
        );

        return $conn;
    }

    /**
     * Crée un mock EntityManager qui délègue find() au repo en mémoire et
     * appelle $repo->remove() lors du flush() pour simuler la suppression DB.
     */
    private function emMock(InMemoryMediaAssetRepository $repo): EntityManagerInterface
    {
        $pendingRemoval = [];

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback(
            static fn (string $class, Uuid $id): ?MediaAsset => $repo->findById($id),
        );
        $em->method('remove')->willReturnCallback(
            static function (object $entity) use (&$pendingRemoval): void {
                if ($entity instanceof MediaAsset) {
                    $pendingRemoval[] = $entity->id();
                }
            },
        );
        $em->method('flush')->willReturnCallback(
            static function () use (&$pendingRemoval, $repo): void {
                foreach ($pendingRemoval as $id) {
                    $repo->remove($id);
                }
                $pendingRemoval = [];
            },
        );

        return $em;
    }

    /**
     * Scenario principal : 60 orphelins sur 60 assets (2 pages de 50).
     *
     * Avec l'ancien code paginé (OFFSET), seuls les 50 premiers auraient été
     * supprimés — les 10 suivants étaient ignorés car l'OFFSET=50 ne trouvait
     * plus rien après les suppressions. Le test vérifie que tous les 60 sont
     * bien traités avec l'approche snapshot.
     */
    public function testAllOrphansDeletedAcrossMultiplePages(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();

        // 60 assets, tous orphelins (> BATCH_SIZE = 50)
        $orphanIds = [];
        for ($i = 0; $i < 60; ++$i) {
            $asset = (new MediaAssetBuilder())
                ->withCreatedAt(sprintf('2026-08-%02dT10:00:00+00:00', ($i % 28) + 1))
                ->build();
            $repo->save($asset);
            $orphanIds[] = $asset->id()->toRfc4122();
        }

        $conn = $this->connectionMock($orphanIds);
        $em   = $this->emMock($repo);

        $tester = new CommandTester($this->makeCommand($repo, $storage, $conn, $em));
        $exit   = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $exit);
        // Tous les 60 assets supprimés — preuve qu'aucun n'est sauté.
        self::assertSame(60, $storage->deleteCallCount, 'Tous les 60 orphelins doivent être supprimés.');
        // Plus aucun asset en mémoire.
        self::assertSame(0, $repo->count());
    }

    public function testMixedOrphansAndReferencedAcrossPages(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();

        $orphanIds = [];
        $allAssets = [];

        // 55 assets : 40 orphelins + 15 référencés, intercalés
        for ($i = 0; $i < 55; ++$i) {
            $asset = (new MediaAssetBuilder())
                ->withCreatedAt(sprintf('2026-08-%02dT%02d:00:00+00:00', ($i % 28) + 1, $i % 24))
                ->build();
            $repo->save($asset);
            $allAssets[] = $asset;

            if ($i % 4 !== 0) { // 3/4 des assets sont orphelins ≈ 41
                $orphanIds[] = $asset->id()->toRfc4122();
            }
        }

        $expectedDeletions = \count($orphanIds);

        $conn = $this->connectionMock($orphanIds);
        $em   = $this->emMock($repo);

        $tester = new CommandTester($this->makeCommand($repo, $storage, $conn, $em));
        $exit   = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertSame($expectedDeletions, $storage->deleteCallCount);
        // Les assets référencés restent en place.
        self::assertSame(55 - $expectedDeletions, $repo->count());
    }

    public function testDryRunDoesNotDeleteAnything(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();

        // 60 orphelins
        $orphanIds = [];
        for ($i = 0; $i < 60; ++$i) {
            $asset = (new MediaAssetBuilder())->build();
            $repo->save($asset);
            $orphanIds[] = $asset->id()->toRfc4122();
        }

        $conn = $this->connectionMock($orphanIds);
        $em   = $this->emMock($repo);

        $tester = new CommandTester($this->makeCommand($repo, $storage, $conn, $em));
        $exit   = $tester->execute(['--dry-run' => true]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertSame(0, $storage->deleteCallCount);
        self::assertSame(60, $repo->count(), 'Le dry-run ne doit rien supprimer.');
    }

    public function testNoOrphansReturnsSuccessWithoutDeletion(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();

        for ($i = 0; $i < 10; ++$i) {
            $repo->save((new MediaAssetBuilder())->build());
        }

        // Aucun orphelin : connection retourne toujours 1 (référencé)
        $conn = $this->connectionMock([]);
        $em   = $this->emMock($repo);

        $tester = new CommandTester($this->makeCommand($repo, $storage, $conn, $em));
        $exit   = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertSame(0, $storage->deleteCallCount);
    }

    public function testAssetAlreadyDeletedBetweenSnapshotAndProcessingIsSkipped(): void
    {
        $repo    = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();

        $asset = (new MediaAssetBuilder())->build();
        $repo->save($asset);

        // Simule un asset supprimé entre le snapshot et le traitement
        // en le retirant du repo AVANT que la commande ne l'inspecte.
        $orphanIds = [$asset->id()->toRfc4122()];
        $repo->remove($asset->id());

        $conn = $this->connectionMock($orphanIds);
        $em   = $this->emMock($repo);

        $tester = new CommandTester($this->makeCommand($repo, $storage, $conn, $em));
        $exit   = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertSame(0, $storage->deleteCallCount, 'Un asset disparu entre snapshot et traitement doit être ignoré.');
    }
}
