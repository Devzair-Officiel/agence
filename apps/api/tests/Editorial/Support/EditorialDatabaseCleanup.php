<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Support;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Utilitaire partagé de nettoyage de la base editorial.
 *
 * Pourquoi c'est nécessaire :
 * - PHPUnit 12 exécute les tests en ordre aléatoire (`executionOrder="random"`).
 * - Les `WebTestCase` (HTTP fonctionnels) committent leurs insertions sans
 *   rollback : sans reset préalable, un test HTTP exécuté avant un test
 *   d'intégration Doctrine ferait fuir des lignes dans la transaction.
 * - Le contrat est explicite ici plutôt que dispersé (DELETE dupliqué) dans
 *   chaque classe de test — le lecteur sait où vit la stratégie et pourquoi.
 *
 * Ce trait ne fait qu'exposer un helper : la mise en place (`setUp`) reste
 * la responsabilité de chaque classe de test, ce qui évite tout effet de
 * bord implicite lors du boot du kernel.
 */
trait EditorialDatabaseCleanup
{
    private function clearEditorialTables(EntityManagerInterface $entityManager): void
    {
        $entityManager->getConnection()->executeStatement('DELETE FROM editorial_article');
    }
}
