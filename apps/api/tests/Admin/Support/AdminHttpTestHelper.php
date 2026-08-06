<?php

declare(strict_types=1);

namespace App\Tests\Admin\Support;

use App\Admin\Application\AdminAccountService;
use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\AdminUser;
use App\Admin\Domain\AdminUserRepositoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Fabrique un admin en base et l'authentifie sur un `KernelBrowser`.
 *
 * Utilisé par tous les tests HTTP de la Phase 8C3 pour éviter de
 * dupliquer le login manuel (crawl + submit). Les tests se concentrent
 * sur le comportement des contrôleurs, pas sur celui du firewall (déjà
 * couvert par `AdminLoginFlowTest`).
 *
 * Le `ContainerInterface` doit être obtenu par l'appelant (accès protégé
 * `WebTestCase::getContainer()`) et transmis explicitement — pattern DI
 * classique.
 */
final class AdminHttpTestHelper
{
    public static function createAndLogin(
        ContainerInterface $container,
        KernelBrowser $client,
        string $email = 'admin@devzair.local',
        string $password = 'DevzairAdmin!2026',
        string $displayName = 'Admin Local',
    ): AdminUser {
        /** @var AdminAccountService $service */
        $service = $container->get(AdminAccountService::class);
        $service->createAdmin($email, $displayName, $password);

        /** @var AdminUserRepositoryInterface $repo */
        $repo = $container->get(AdminUserRepositoryInterface::class);
        $admin = $repo->findByNormalizedEmail(AdminEmail::fromString($email)->normalized());
        \assert($admin instanceof AdminUser, \sprintf('Admin %s introuvable en base après création.', $email));

        // Le firewall `admin` est le seul actif sur `^/admin`. On l'indique
        // explicitement pour que `loginUser` place le token dans la bonne
        // partie de la session (sinon Symfony choisit la première firewall
        // rencontrée, ce qui ne fonctionne pas si plusieurs sont déclarées).
        $client->loginUser($admin, 'admin');

        return $admin;
    }
}
