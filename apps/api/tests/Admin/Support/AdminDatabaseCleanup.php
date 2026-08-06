<?php

declare(strict_types=1);

namespace App\Tests\Admin\Support;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Purge la table `admin_user` avant chaque test intégration : les tests
 * s'exécutent en ordre aléatoire (`executionOrder="random"`), il faut
 * repartir d'une base propre pour éviter les collisions d'email unique.
 */
final class AdminDatabaseCleanup
{
    public static function purge(EntityManagerInterface $em): void
    {
        /** @var Connection $connection */
        $connection = $em->getConnection();
        $connection->executeStatement('TRUNCATE TABLE admin_user');
        $em->clear();
    }
}
