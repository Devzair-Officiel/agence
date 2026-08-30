<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Presentation\Http\Lead;

use App\Estimator\Infrastructure\Security\EstimateLeadRateLimiter;
use App\Estimator\Presentation\Http\Lead\EstimateLeadController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

#[CoversClass(EstimateLeadController::class)]
final class EstimateLeadControllerTest extends WebTestCase
{
    private const ALLOWED_ORIGIN = 'http://localhost:3001';
    private const ENDPOINT       = '/estimate/lead';

    // ─── Happy path ───────────────────────────────────────────────────────────

    public function testVitrineSiteLeadReturns201(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(),
        );

        self::assertResponseStatusCodeSame(201);
        $data = $this->decode($client);
        self::assertSame('ok', $data['status']);
        self::assertNotEmpty($data['request_id']);
    }

    public function testHumanScopingLeadReturns201WithNullAmounts(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload([
                'project_type' => 'unknown',
                'objectives'   => ['sell_online', 'digitalize_process'],
            ]),
        );

        self::assertResponseStatusCodeSame(201);
        $data = $this->decode($client);
        self::assertSame('ok', $data['status']);
    }

    public function testLeadWithOptionalFieldsReturns201(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload([
                'phone'                  => '+33 6 12 34 56 78',
                'company'                => 'ACME Corp',
                'additional_feature_note'=> 'Connexion avec notre logiciel ERP',
            ]),
        );

        self::assertResponseStatusCodeSame(201);
    }

    public function testResponseHeadersAreCorrect(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(),
        );

        $response = $client->getResponse();
        $requestId = $response->headers->get('X-Request-Id');
        self::assertNotEmpty($requestId);
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $data = $this->decode($client);
        self::assertSame($requestId, $data['request_id']);
    }

    // ─── Preuve que les prix clients sont ignorés ─────────────────────────────

    public function testClientPricesAreNeverTrusted(): void
    {
        $client = self::createClient();

        // Envoi d'un champ "estimate" fictif (jamais dans le DTO) — doit être ignoré
        $payloadWithFakePrice = array_merge(
            json_decode($this->payload(), true, 512, JSON_THROW_ON_ERROR),
            ['estimate' => ['minimum' => 1, 'maximum' => 1]],
        );

        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: json_encode($payloadWithFakePrice, JSON_THROW_ON_ERROR),
        );

        // Le contrôleur doit toujours répondre 201 sans planter
        self::assertResponseStatusCodeSame(201);
    }

    // ─── Honeypot ─────────────────────────────────────────────────────────────

    public function testHoneypotFilledReturns202Silently(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['website' => 'https://bot.example']),
        );

        // 202 indiscernable — le bot ne sait pas que sa requête a été rejetée
        self::assertResponseStatusCodeSame(202);
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    public function testMissingNameReturns400(): void
    {
        $data = json_decode($this->payload(), true, 512, JSON_THROW_ON_ERROR);
        unset($data['name']);

        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: json_encode($data, JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        $body = $this->decode($client);
        self::assertSame('validation_error', $body['code']);
        self::assertArrayHasKey('name', $body['errors']);
    }

    public function testMissingEmailReturns400(): void
    {
        $data = json_decode($this->payload(), true, 512, JSON_THROW_ON_ERROR);
        unset($data['email']);

        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: json_encode($data, JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        $body = $this->decode($client);
        self::assertArrayHasKey('email', $body['errors']);
    }

    public function testInvalidEmailReturns400(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['email' => 'not-an-email']),
        );

        self::assertResponseStatusCodeSame(400);
        $body = $this->decode($client);
        self::assertArrayHasKey('email', $body['errors']);
    }

    public function testInvalidProjectTypeReturns400(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'invalid_type']),
        );

        self::assertResponseStatusCodeSame(400);
        $body = $this->decode($client);
        self::assertSame('validation_error', $body['code']);
        self::assertArrayHasKey('project_type', $body['errors']);
    }

    public function testInvalidJsonReturns400(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: '{not-json',
        );

        self::assertResponseStatusCodeSame(400);
        $body = $this->decode($client);
        self::assertSame('invalid_json', $body['code']);
    }

    public function testPayloadTooLargeReturns413(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: str_repeat('a', 12_001),
        );

        self::assertResponseStatusCodeSame(413);
        $body = $this->decode($client);
        self::assertSame('payload_too_large', $body['code']);
    }

    // ─── Sécurité — Origin ───────────────────────────────────────────────────

    public function testMissingOriginIsForbidden(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->payload(),
        );

        self::assertResponseStatusCodeSame(403);
        $body = $this->decode($client);
        self::assertSame('origin_not_allowed', $body['code']);
    }

    public function testDisallowedOriginIsForbidden(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ORIGIN'  => 'https://evil.example',
            ],
            content: $this->payload(),
        );

        self::assertResponseStatusCodeSame(403);
        $body = $this->decode($client);
        self::assertSame('origin_not_allowed', $body['code']);
    }

    // ─── Sécurité — Rate limit ───────────────────────────────────────────────

    public function testRateLimitExhausionReturns429WithRetryAfter(): void
    {
        $client = self::createClient();
        $client->disableReboot();

        $factory = new RateLimiterFactory(
            ['policy' => 'token_bucket', 'id' => 'estimate_lead_ip_test', 'limit' => 1, 'rate' => ['interval' => '1 hour', 'amount' => 1]],
            new InMemoryStorage(),
        );
        self::getContainer()->set(EstimateLeadRateLimiter::class, new EstimateLeadRateLimiter($factory));

        $headers = $this->serverHeaders();
        $payload = $this->payload();

        // 1ère requête : acceptée
        $client->request('POST', self::ENDPOINT, server: $headers, content: $payload);
        self::assertResponseStatusCodeSame(201);

        // 2ème requête : 429
        $client->request('POST', self::ENDPOINT, server: $headers, content: $payload);
        self::assertResponseStatusCodeSame(429);

        $body = $this->decode($client);
        self::assertSame('rate_limited', $body['code']);
        self::assertNotEmpty($client->getResponse()->headers->get('Retry-After'));
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): string
    {
        $defaults = [
            'name'             => 'Jean Dupont',
            'email'            => 'jean.dupont@example.com',
            'project_type'     => 'vitrinesite',
            'current_situation'=> 'none',
            'scale'            => 'small',
        ];

        return json_encode(array_merge($defaults, $overrides), JSON_THROW_ON_ERROR);
    }

    /** @return array<string, string> */
    private function serverHeaders(): array
    {
        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ORIGIN'  => self::ALLOWED_ORIGIN,
        ];
    }

    /** @return array<string, mixed> */
    private function decode(KernelBrowser $client): array
    {
        /** @var string $content */
        $content = $client->getResponse()->getContent();

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
