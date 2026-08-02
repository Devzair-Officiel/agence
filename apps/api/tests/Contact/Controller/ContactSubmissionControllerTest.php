<?php

declare(strict_types=1);

namespace App\Tests\Contact\Controller;

use App\Contact\Controller\ContactSubmissionController;
use App\Contact\Service\InMemoryContactMessageSender;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(ContactSubmissionController::class)]
final class ContactSubmissionControllerTest extends WebTestCase
{
    private const ALLOWED_ORIGIN = 'http://localhost:3001';

    public function testValidSubmissionIsAccepted(): void
    {
        $client = self::createClient();
        $sender = self::getInMemorySender($client);

        $client->request(
            'POST',
            '/contact',
            server: $this->serverHeaders(),
            content: $this->validPayload(),
        );

        self::assertResponseStatusCodeSame(200);

        $data = $this->decode($client);
        self::assertSame('accepted', $data['status']);
        self::assertNotEmpty($data['request_id']);
        self::assertSame($data['request_id'], $client->getResponse()->headers->get('X-Request-Id'));

        self::assertCount(1, $sender->all(), 'Un et un seul message doit être envoyé.');
        self::assertSame('alice@example.com', $sender->all()[0]['request']->email);
    }

    public function testMissingOriginIsForbidden(): void
    {
        $client = self::createClient();
        $sender = self::getInMemorySender($client);

        $client->request(
            'POST',
            '/contact',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->validPayload(),
        );

        self::assertResponseStatusCodeSame(403);

        $data = $this->decode($client);
        self::assertSame('origin_not_allowed', $data['code']);
        self::assertCount(0, $sender->all(), 'Aucun email envoyé sans Origin.');
    }

    public function testDisallowedOriginIsForbidden(): void
    {
        $client = self::createClient();
        $sender = self::getInMemorySender($client);

        $client->request(
            'POST',
            '/contact',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ORIGIN' => 'https://evil.example',
            ],
            content: $this->validPayload(),
        );

        self::assertResponseStatusCodeSame(403);
        self::assertCount(0, $sender->all());
    }

    public function testHoneypotTriggeredReturnsGenericAcceptedWithoutSending(): void
    {
        $client = self::createClient();
        $sender = self::getInMemorySender($client);

        $payload = json_decode($this->validPayload(), true, flags: \JSON_THROW_ON_ERROR);
        $payload['website'] = 'https://spam.example';

        $client->request(
            'POST',
            '/contact',
            server: $this->serverHeaders(),
            content: json_encode($payload, \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(202);
        self::assertCount(0, $sender->all(), 'Le honeypot ne doit jamais envoyer.');

        $data = $this->decode($client);
        self::assertSame('accepted', $data['status'], 'Réponse volontairement identique au succès.');
    }

    public function testValidationErrorIsReported(): void
    {
        $client = self::createClient();
        $sender = self::getInMemorySender($client);

        $payload = json_decode($this->validPayload(), true, flags: \JSON_THROW_ON_ERROR);
        $payload['email'] = 'not-an-email';
        $payload['message'] = 'trop court';
        $payload['consent'] = false;

        $client->request(
            'POST',
            '/contact',
            server: $this->serverHeaders(),
            content: json_encode($payload, \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        $data = $this->decode($client);
        self::assertSame('validation_failed', $data['code']);
        self::assertArrayHasKey('email', $data['errors']);
        self::assertArrayHasKey('message', $data['errors']);
        self::assertArrayHasKey('consent', $data['errors']);

        self::assertCount(0, $sender->all());
    }

    public function testInvalidJsonReturns400(): void
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/contact',
            server: $this->serverHeaders(),
            content: '{not-json',
        );

        self::assertResponseStatusCodeSame(400);
        $data = $this->decode($client);
        self::assertSame('invalid_json', $data['code']);
    }

    public function testPayloadTooLargeIsRejected(): void
    {
        $client = self::createClient();
        $sender = self::getInMemorySender($client);

        $payload = json_decode($this->validPayload(), true, flags: \JSON_THROW_ON_ERROR);
        $payload['message'] = str_repeat('a', 20_000);

        $client->request(
            'POST',
            '/contact',
            server: $this->serverHeaders(),
            content: json_encode($payload, \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(413);
        self::assertCount(0, $sender->all());
    }

    public function testMailerFailureReturns503TemporaryErrorWithoutLosingUserPayload(): void
    {
        $client = self::createClient();
        $sender = self::getInMemorySender($client);
        $sender->failNextTemporarily();

        $client->request(
            'POST',
            '/contact',
            server: $this->serverHeaders(),
            content: $this->validPayload(),
        );

        self::assertResponseStatusCodeSame(503);
        self::assertCount(0, $sender->all(), 'Aucun message n\'a été « accepté » : le sender a levé.');

        $data = $this->decode($client);
        self::assertSame('error', $data['status'], 'Le client doit voir un status "error", pas "accepted".');
        self::assertSame('temporary_error', $data['code']);
        self::assertNotEmpty($data['request_id']);
    }

    public function testResponseHeadersIncludeRequestIdAndNoStore(): void
    {
        $client = self::createClient();
        $client->request('POST', '/contact', server: $this->serverHeaders(), content: $this->validPayload());

        $response = $client->getResponse();
        self::assertNotEmpty($response->headers->get('X-Request-Id'));
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    private function validPayload(): string
    {
        return json_encode([
            'name' => 'Alice Dupont',
            'email' => 'alice@example.com',
            'company' => 'Acme',
            'telephone' => '+33 6 12 34 56 78',
            'projectType' => 'refonte',
            'message' => 'Nous souhaitons refondre notre site vitrine et améliorer notre SEO local.',
            'consent' => true,
            'website' => '',
            'turnstileToken' => 'dev-noop-token',
        ], \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, string>
     */
    private function serverHeaders(): array
    {
        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ORIGIN' => self::ALLOWED_ORIGIN,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(KernelBrowser $client): array
    {
        return json_decode(
            (string) $client->getResponse()->getContent(),
            true,
            flags: \JSON_THROW_ON_ERROR,
        );
    }

    private static function getInMemorySender(KernelBrowser $client): InMemoryContactMessageSender
    {
        /** @var InMemoryContactMessageSender $sender */
        $sender = $client->getContainer()->get(InMemoryContactMessageSender::class);
        $sender->reset();

        return $sender;
    }
}
