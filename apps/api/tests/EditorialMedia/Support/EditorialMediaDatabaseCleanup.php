<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Support;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Vidage de la table `editorial_media_asset` avant chaque test intégration.
 *
 * Même contrat que `EditorialDatabaseCleanup` (voir `tests/Editorial/Support`) :
 * PHPUnit 12 exécute les tests dans un ordre aléatoire, et les tests HTTP
 * committent leurs insertions sans rollback. Sans reset préalable, un
 * `AdminMediaListControllerTest` exécuté après un autre test médias verrait
 * fuir des lignes.
 */
trait EditorialMediaDatabaseCleanup
{
    private function clearEditorialMediaTables(EntityManagerInterface $entityManager): void
    {
        $entityManager->getConnection()->executeStatement('DELETE FROM editorial_media_asset');
    }
}
