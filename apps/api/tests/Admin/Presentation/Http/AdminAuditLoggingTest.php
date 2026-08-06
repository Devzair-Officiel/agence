<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Admin\Application\AdminAccountService;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie que le canal Monolog `admin` capture les événements auth sans PII :
 * - `admin.login_success` avec `admin_id` (UUID), sans email ;
 * - `admin.login_failure` avec `reason` (classe courte), sans email.
 */
final class AdminAuditLoggingTest extends WebTestCase
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

    public function testSuccessfulLoginEmitsAdminInfoRecord(): void
    {
        $this->submit(self::EMAIL, self::PASSWORD);
        self::assertResponseRedirects('/admin');

        $handler = $this->adminTestHandler();
        self::assertTrue(
            $handler->hasInfoThatContains('admin.login_success'),
            'admin.login_success must be logged as INFO on the admin channel.',
        );

        // Le contexte du canal admin doit contenir admin_id (UUID) et aucune PII.
        // Le canal `security` natif de Symfony logue quant à lui le token (email
        // inclus) : ce test ne fait pas foi sur ce canal, qui n'est pas dans le
        // périmètre applicatif (voir docs/05-SECURITY-PRIVACY.md pour la
        // recommandation de niveau prod).
        $adminOnly = array_filter(
            $handler->getRecords(),
            static fn ($r): bool => ($r instanceof \Monolog\LogRecord ? $r->channel : ($r['channel'] ?? null)) === 'admin',
        );
        $flat = json_encode(array_values($adminOnly), \JSON_THROW_ON_ERROR);
        self::assertStringContainsString('admin_id', $flat);
        self::assertStringNotContainsString(self::EMAIL, $flat);
    }

    public function testFailedLoginEmitsAdminWarningWithoutIdentifier(): void
    {
        $this->submit(self::EMAIL, 'WrongPassword12345');
        self::assertResponseRedirects('/admin/login');

        $handler = $this->adminTestHandler();
        self::assertTrue(
            $handler->hasWarningThatContains('admin.login_failure'),
            'admin.login_failure must be logged as WARNING on the admin channel.',
        );

        $adminOnly = array_filter(
            $handler->getRecords(),
            static fn ($r): bool => ($r instanceof \Monolog\LogRecord ? $r->channel : ($r['channel'] ?? null)) === 'admin',
        );
        $flat = json_encode(array_values($adminOnly), \JSON_THROW_ON_ERROR);
        self::assertStringContainsString('reason', $flat);
        self::assertStringNotContainsString(self::EMAIL, $flat, 'Aucun identifiant utilisateur ne doit apparaître sur le canal admin.');
        self::assertStringNotContainsString('WrongPassword', $flat, 'Le mot de passe tapé ne doit jamais fuir.');
    }

    public function testLogoutEmitsAdminInfoRecord(): void
    {
        $crawler = $this->client->request('GET', '/admin/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        $logoutForm = $crawler->selectButton('Se déconnecter')->form();
        $this->client->submit($logoutForm);

        $handler = $this->adminTestHandler();
        self::assertTrue(
            $handler->hasInfoThatContains('admin.logout'),
            'admin.logout must be logged as INFO on the admin channel.',
        );
    }

    private function submit(string $email, string $password): void
    {
        $crawler = $this->client->request('GET', '/admin/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => $email,
            'password' => $password,
        ]);
        $this->client->submit($form);
    }

    private function adminTestHandler(): TestHandler
    {
        /** @var iterable<HandlerInterface> $handlers */
        $handlers = self::getContainer()->get('monolog.logger.admin')->getHandlers();
        foreach ($handlers as $handler) {
            if ($handler instanceof TestHandler) {
                return $handler;
            }
        }
        self::fail('Aucun TestHandler branché sur le canal "admin" — vérifie packages/monolog.yaml.');
    }
}
