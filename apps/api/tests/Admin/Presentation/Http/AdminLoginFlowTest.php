<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Admin\Application\AdminAccountService;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Bout-à-bout HTTP (WebTestCase) du flow auth Phase 8C1 :
 * - GET /admin/login expose un formulaire avec CSRF ;
 * - POST /admin/login valide redirige vers /admin ;
 * - POST /admin/login invalide reste sur /admin/login (message générique) ;
 * - GET /admin sans session redirige vers /admin/login ;
 * - un compte désactivé ne peut jamais s'authentifier.
 *
 * Chaque test purge `admin_user` avant de démarrer pour isoler l'état.
 */
final class AdminLoginFlowTest extends WebTestCase
{
    private const EMAIL = 'admin@devzair.local';
    private const PASSWORD = 'DevzairAdmin!2026';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = self::getContainer();
        AdminDatabaseCleanup::purge($container->get(EntityManagerInterface::class));
    }

    public function testValidCredentialsRedirectToDashboard(): void
    {
        $this->createAdmin();

        $this->submitLogin(self::EMAIL, self::PASSWORD);

        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Tableau de bord', (string) $this->client->getResponse()->getContent());
    }

    public function testInvalidPasswordStaysOnLoginWithGenericMessage(): void
    {
        $this->createAdmin();

        $this->submitLogin(self::EMAIL, 'WrongPassword12345');

        self::assertResponseRedirects('/admin/login');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        $body = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Identifiants invalides', $body);
        // Le message ne doit pas révéler si l'email existe :
        self::assertStringNotContainsString('inconnu', $body);
    }

    public function testDashboardWithoutSessionRedirectsToLogin(): void
    {
        $this->client->request('GET', '/admin');

        self::assertResponseRedirects();
        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/admin/login', $location);
    }

    public function testDisabledAdminCannotLogin(): void
    {
        $this->createAdmin();
        $service = self::getContainer()->get(AdminAccountService::class);
        $service->disableAdmin(self::EMAIL);

        $this->submitLogin(self::EMAIL, self::PASSWORD);

        self::assertResponseRedirects('/admin/login');
    }

    public function testUnknownEmailIsRejectedWithSameMessage(): void
    {
        $this->submitLogin('ghost@example.com', self::PASSWORD);

        self::assertResponseRedirects('/admin/login');
        $this->client->followRedirect();
        $body = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Identifiants invalides', $body);
    }

    public function testSessionIdentifierRotatesOnSuccessfulLogin(): void
    {
        $this->createAdmin();

        // Un premier GET pour établir un cookie de session anonyme : /admin/login
        // démarre la session côté serveur (le token CSRF y est stocké).
        $this->client->request('GET', '/admin/login');
        $preLoginId = $this->firstSessionCookieValue();

        $this->submitLogin(self::EMAIL, self::PASSWORD);
        $postLoginId = $this->firstSessionCookieValue();

        // Comportement documenté de `session_fixation_strategy: migrate` :
        // un identifiant de session différent doit être émis après login.
        // En test, le storage `mock_file` utilise le nom `MOCKSESSID` (hardcodé
        // par Symfony) ; en prod la config `framework.session.name` s'applique
        // et le cookie s'appelle `DZ_ADMIN_SESSID`.
        self::assertNotNull($postLoginId);
        if ($preLoginId !== null) {
            self::assertNotSame($preLoginId, $postLoginId);
        }
    }

    private function firstSessionCookieValue(): ?string
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if ($this->isSessionCookieName($cookie->getName())) {
                return $cookie->getValue();
            }
        }
        foreach ($this->client->getCookieJar()->all() as $cookie) {
            if ($this->isSessionCookieName($cookie->getName())) {
                return $cookie->getValue();
            }
        }

        return null;
    }

    private function isSessionCookieName(string $name): bool
    {
        return $name === 'DZ_ADMIN_SESSID' || $name === 'MOCKSESSID';
    }

    private function createAdmin(): void
    {
        $service = self::getContainer()->get(AdminAccountService::class);
        $service->createAdmin(self::EMAIL, 'Admin Local', self::PASSWORD);
    }

    private function submitLogin(string $email, string $password): void
    {
        $crawler = $this->client->request('GET', '/admin/login');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => $email,
            'password' => $password,
        ]);
        $this->client->submit($form);
    }
}
