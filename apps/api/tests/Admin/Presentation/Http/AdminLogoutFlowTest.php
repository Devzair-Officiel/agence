<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Admin\Application\AdminAccountService;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie que POST /admin/logout invalide bien la session et interdit tout
 * accès ultérieur au dashboard sans réauthentification.
 */
final class AdminLogoutFlowTest extends WebTestCase
{
    private const EMAIL = 'admin@devzair.local';
    private const PASSWORD = 'DevzairAdmin!2026';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        AdminDatabaseCleanup::purge(self::getContainer()->get(EntityManagerInterface::class));
        self::getContainer()->get(AdminAccountService::class)
            ->createAdmin(self::EMAIL, 'Admin Local', self::PASSWORD);
    }

    public function testLogoutInvalidatesSession(): void
    {
        $crawler = $this->client->request('GET', '/admin/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/admin');
        $crawler = $this->client->followRedirect();
        self::assertResponseIsSuccessful();

        $logoutForm = $crawler->selectButton('Se déconnecter')->form();
        $this->client->submit($logoutForm);
        self::assertResponseRedirects();

        // Après logout, une requête sur /admin doit repartir vers /admin/login.
        $this->client->request('GET', '/admin');
        self::assertResponseRedirects();
        self::assertStringContainsString(
            '/admin/login',
            (string) $this->client->getResponse()->headers->get('Location'),
        );
    }
}
