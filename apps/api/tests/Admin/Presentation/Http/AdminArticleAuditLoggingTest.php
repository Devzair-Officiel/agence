<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie que le canal Monolog `admin` capture les événements article sans
 * fuite de PII (email, mot de passe, contenu Markdown). Les tests sont
 * volontairement minimaux : ils prouvent que le fil de log est branché et
 * que les grands invariants (UUID admin, article_id, absence d'email)
 * tiennent — pas la structure exhaustive de chaque payload (couverte par
 * les tests unitaires du logger).
 */
final class AdminArticleAuditLoggingTest extends WebTestCase
{
    use EditorialDatabaseCleanup;

    private const EMAIL = 'admin@devzair.local';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        AdminDatabaseCleanup::purge($em);
        $this->clearEditorialTables($em);
    }

    public function testCreatedActionLogsAdminIdAndArticleIdWithoutEmail(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client, self::EMAIL);

        $crawler = $this->client->request('GET', '/admin/articles/new');
        $token = $crawler->filterXPath('//input[@name="_csrf_token"]')->attr('value');
        \assert(\is_string($token));

        $this->client->request('POST', '/admin/articles/new', [
            '_csrf_token' => $token,
            'slug' => 'test-audit-created',
            'title' => 'Titre pour test audit',
            'excerpt' => 'Un chapô long assez pour dépasser la borne minimale imposée par le domaine.',
            'body_markdown' => "## Intro\n\nCorps Markdown de base — suffisamment long pour être crédible.",
            'seo_title' => 'Titre SEO test audit — assez de caractères',
            'seo_description' => 'Description SEO — assez longue pour dépasser la borne minimale imposée à un article.',
            'author_name' => 'Devzair',
            'author_type' => 'organization',
            'expertises' => ['concevoir'],
        ]);
        self::assertResponseRedirects();

        $flat = $this->adminChannelJson();
        self::assertStringContainsString('admin.article.created', $flat);
        self::assertStringContainsString('admin_id', $flat);
        self::assertStringContainsString('article_id', $flat);
        self::assertStringNotContainsString(self::EMAIL, $flat, 'Le canal admin ne doit jamais logger l\'email.');
    }

    public function testActionFailedIsWarningWithoutMarkdownBody(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client, self::EMAIL);

        // Slug volontairement invalide (majuscules interdites) — la validation
        // du VO déclenche `ArticleInvariantViolation` → `actionFailed`.
        $crawler = $this->client->request('GET', '/admin/articles/new');
        $token = $crawler->filterXPath('//input[@name="_csrf_token"]')->attr('value');
        \assert(\is_string($token));

        $secret = "## SecretMarkdownContent\n\n<script>alert('exfil')</script>";
        $this->client->request('POST', '/admin/articles/new', [
            '_csrf_token' => $token,
            'slug' => 'INVALID SLUG',
            'title' => 'Titre valide',
            'excerpt' => 'Un chapô long assez pour dépasser la borne minimale imposée par le domaine.',
            'body_markdown' => $secret,
            'seo_title' => 'Titre SEO test audit — assez de caractères',
            'seo_description' => 'Description SEO — assez longue pour dépasser la borne minimale imposée à un article.',
            'author_name' => 'Devzair',
            'author_type' => 'organization',
            'expertises' => ['concevoir'],
        ]);

        $flat = $this->adminChannelJson();
        self::assertStringContainsString('admin.article.action_failed', $flat);
        self::assertStringNotContainsString('SecretMarkdownContent', $flat, 'Le Markdown soumis ne doit jamais fuir sur le canal admin.');
        self::assertStringNotContainsString(self::EMAIL, $flat);
    }

    public function testTwoAdminsHaveIndependentRateLimitBuckets(): void
    {
        // `KernelBrowser` reboote le kernel entre chaque requête, ce qui
        // recréerait un `InMemoryStorage` neuf pour le rate limiter à chaque
        // appel. On désactive donc le reboot pour maintenir le compteur
        // stable au fil des 60+ requêtes de Bob, puis on inspecte directement
        // le limiteur pour valider l'isolation par UUID.
        $this->client->disableReboot();

        $bob = AdminHttpTestHelper::createAndLogin(
            self::getContainer(),
            $this->client,
            'bob@devzair.local',
        );
        $carol = AdminHttpTestHelper::createAndLogin(
            self::getContainer(),
            $this->client,
            'carol@devzair.local',
        );
        self::assertNotSame($bob->id()->toRfc4122(), $carol->id()->toRfc4122());

        /** @var \App\Admin\Infrastructure\Security\AdminActionRateLimiter $limiter */
        $limiter = self::getContainer()->get(\App\Admin\Infrastructure\Security\AdminActionRateLimiter::class);

        // On consomme 60 jetons du compteur `admin_write:{bob}`.
        for ($i = 0; $i < 60; ++$i) {
            $rl = $limiter->consumeWrite($bob);
            self::assertTrue($rl->isAccepted(), \sprintf('Bob token %d refusé prématurément.', $i));
        }
        // Le 61e doit être refusé.
        self::assertFalse($limiter->consumeWrite($bob)->isAccepted());

        // Carol garde ses 60 tokens (isolation par UUID).
        self::assertTrue($limiter->consumeWrite($carol)->isAccepted());

        // Publish limiter est indépendant du write limiter :
        for ($i = 0; $i < 20; ++$i) {
            self::assertTrue($limiter->consumePublish($bob)->isAccepted());
        }
        self::assertFalse($limiter->consumePublish($bob)->isAccepted());
        // Carol a encore son quota publish intact :
        self::assertTrue($limiter->consumePublish($carol)->isAccepted());
    }

    private function adminChannelJson(): string
    {
        $handler = $this->adminTestHandler();
        $records = array_filter(
            $handler->getRecords(),
            static fn ($r): bool => ($r instanceof \Monolog\LogRecord ? $r->channel : ($r['channel'] ?? null)) === 'admin',
        );

        return json_encode(array_values($records), \JSON_THROW_ON_ERROR);
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
        self::fail('Aucun TestHandler branché sur le canal admin.');
    }
}
