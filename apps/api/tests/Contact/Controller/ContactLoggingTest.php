<?php

declare(strict_types=1);

namespace App\Tests\Contact\Controller;

use App\Contact\Controller\ContactSubmissionController;
use App\Contact\Service\InMemoryContactMessageSender;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Garantit qu'aucun log produit par le pipeline /api/contact ne contient
 * de PII : message, email visiteur, téléphone, secret Turnstile, token.
 */
#[CoversClass(ContactSubmissionController::class)]
final class ContactLoggingTest extends WebTestCase
{
    private const ALLOWED_ORIGIN = 'http://localhost:3001';
    private const SECRET_MESSAGE = 'Contenu confidentiel — ne doit jamais fuir dans les logs.';
    private const SECRET_EMAIL = 'confidentiel-visiteur@example.com';
    private const SECRET_PHONE = '+33 6 99 88 77 66';
    private const SECRET_TOKEN = 'super-secret-turnstile-token-visitor';

    public function testAcceptedSubmissionDoesNotLogPii(): void
    {
        $client = self::createClient();

        $payload = json_encode([
            'name' => 'Alice Dupont',
            'email' => self::SECRET_EMAIL,
            'company' => 'Acme',
            'telephone' => self::SECRET_PHONE,
            'projectType' => 'refonte',
            'message' => self::SECRET_MESSAGE.' '.str_repeat('x', 20),
            'consent' => true,
            'website' => '',
            'turnstileToken' => self::SECRET_TOKEN,
        ], \JSON_THROW_ON_ERROR);

        $client->request(
            'POST',
            '/contact',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ORIGIN' => self::ALLOWED_ORIGIN,
            ],
            content: $payload,
        );

        self::assertResponseStatusCodeSame(200);

        $handler = self::getTestLogHandler($client);
        $flat = json_encode($handler->getRecords(), \JSON_THROW_ON_ERROR);

        self::assertStringNotContainsString(self::SECRET_MESSAGE, $flat, 'Le message visiteur ne doit jamais être loggué.');
        self::assertStringNotContainsString(self::SECRET_EMAIL, $flat, 'L\'email visiteur ne doit jamais être loggué.');
        self::assertStringNotContainsString(self::SECRET_PHONE, $flat, 'Le téléphone ne doit jamais être loggué.');
        self::assertStringNotContainsString(self::SECRET_TOKEN, $flat, 'Le token Turnstile ne doit jamais être loggué.');
    }

    public function testMailerFailureLogsWarningWithoutPii(): void
    {
        $client = self::createClient();

        /** @var InMemoryContactMessageSender $sender */
        $sender = $client->getContainer()->get(InMemoryContactMessageSender::class);
        $sender->reset();
        $sender->failNextTemporarily();

        $payload = json_encode([
            'name' => 'Alice Dupont',
            'email' => self::SECRET_EMAIL,
            'company' => 'Acme',
            'telephone' => self::SECRET_PHONE,
            'projectType' => 'refonte',
            'message' => self::SECRET_MESSAGE.' '.str_repeat('x', 20),
            'consent' => true,
            'website' => '',
            'turnstileToken' => self::SECRET_TOKEN,
        ], \JSON_THROW_ON_ERROR);

        $client->request(
            'POST',
            '/contact',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ORIGIN' => self::ALLOWED_ORIGIN,
            ],
            content: $payload,
        );

        self::assertResponseStatusCodeSame(503);

        $handler = self::getTestLogHandler($client);

        self::assertTrue(
            $handler->hasWarningThatContains('contact.mailer_unavailable'),
            'L\'échec du sender doit produire un warning canal contact avec le libellé contact.mailer_unavailable.',
        );

        $flat = json_encode($handler->getRecords(), \JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString(self::SECRET_MESSAGE, $flat);
        self::assertStringNotContainsString(self::SECRET_EMAIL, $flat);
        self::assertStringNotContainsString(self::SECRET_PHONE, $flat);
        self::assertStringNotContainsString(self::SECRET_TOKEN, $flat);
    }

    private static function getTestLogHandler(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): \Symfony\Bridge\Monolog\Handler\ConsoleHandler|\Monolog\Handler\TestHandler
    {
        /** @var iterable<\Monolog\Handler\HandlerInterface> $handlers */
        $handlers = $client->getContainer()->get('monolog.logger.contact')->getHandlers();

        foreach ($handlers as $handler) {
            if ($handler instanceof \Monolog\Handler\TestHandler) {
                return $handler;
            }
        }

        self::fail('Aucun TestHandler branché sur le canal "contact" — vérifie packages/monolog.yaml.');
    }
}
