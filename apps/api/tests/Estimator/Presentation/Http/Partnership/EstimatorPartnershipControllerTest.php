<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Presentation\Http\Partnership;

use App\Estimator\Infrastructure\Security\EstimatePartnershipRateLimiter;
use App\Estimator\Presentation\Http\Partnership\EstimatorPartnershipController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

#[CoversClass(EstimatorPartnershipController::class)]
final class EstimatorPartnershipControllerTest extends WebTestCase
{
    private const ALLOWED_ORIGIN = 'http://localhost:3001';
    private const ENDPOINT       = '/estimate/partnership';

    // ─── Happy path ───────────────────────────────────────────────────────────

    public function testVitrineSitePartnershipReturns201(): void
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
        self::assertSame('submitted', $data['status']);
        self::assertNotEmpty($data['request_id']);
        self::assertNotEmpty($data['proposal_id']);
    }

    public function testHumanScopingPartnershipReturns201WithNullAmounts(): void
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
        self::assertSame('submitted', $data['status']);
    }

    public function testPartnershipWithOptionalFieldsReturns201(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload([
                'phone'             => '+33 6 12 34 56 78',
                'company'           => 'Startup SAS',
                'lead_request_id'   => 'req-lead-abc123def',
                'generates_revenue' => 'not_yet',
                'relevance_text'    => 'Notre secteur est en forte croissance.',
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

        $response  = $client->getResponse();
        $requestId = $response->headers->get('X-Request-Id');
        self::assertNotEmpty($requestId);
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $data = $this->decode($client);
        self::assertSame($requestId, $data['request_id']);
    }

    // ─── Prix frontend ignorés ────────────────────────────────────────────────

    public function testClientPricesAreNeverTrusted(): void
    {
        $client = self::createClient();

        $payloadWithFakePrice = array_merge(
            json_decode($this->payload(), true, 512, JSON_THROW_ON_ERROR),
            [
                'estimate'        => ['minimum' => 1, 'maximum' => 1],
                'pricing_version' => 'fake-v0',
                'one_off_items'   => [['code' => 'BASE', 'minimum' => 1, 'maximum' => 1]],
            ],
        );

        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: json_encode($payloadWithFakePrice, JSON_THROW_ON_ERROR),
        );

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

    public function testMissingPartnershipTypeReturns400(): void
    {
        $data = json_decode($this->payload(), true, 512, JSON_THROW_ON_ERROR);
        unset($data['partnership_type']);

        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: json_encode($data, JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        $body = $this->decode($client);
        self::assertArrayHasKey('partnership_type', $body['errors']);
    }

    public function testInvalidPartnershipTypeReturns400(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['partnership_type' => 'equity_for_free']),
        );

        self::assertResponseStatusCodeSame(400);
        $body = $this->decode($client);
        self::assertSame('validation_error', $body['code']);
        self::assertArrayHasKey('partnership_type', $body['errors']);
    }

    public function testMissingProposalTextReturns400(): void
    {
        $data = json_decode($this->payload(), true, 512, JSON_THROW_ON_ERROR);
        unset($data['proposal_text']);

        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: json_encode($data, JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        $body = $this->decode($client);
        self::assertArrayHasKey('proposal_text', $body['errors']);
    }

    public function testProposalTextTooLongReturns400(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['proposal_text' => str_repeat('x', 501)]),
        );

        self::assertResponseStatusCodeSame(400);
        $body = $this->decode($client);
        self::assertArrayHasKey('proposal_text', $body['errors']);
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
            content: str_repeat('a', 14_001),
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
            ['policy' => 'token_bucket', 'id' => 'partnership_ip_test', 'limit' => 1, 'rate' => ['interval' => '1 hour', 'amount' => 1]],
            new InMemoryStorage(),
        );
        self::getContainer()->set(EstimatePartnershipRateLimiter::class, new EstimatePartnershipRateLimiter($factory));

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

    // ─── Pas de calcul automatique de parts ──────────────────────────────────

    public function testNoAutomaticPartnershipCalculationInResponse(): void
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

        // La réponse ne contient JAMAIS de calcul de parts
        self::assertArrayNotHasKey('percentage', $data);
        self::assertArrayNotHasKey('equity', $data);
        self::assertArrayNotHasKey('commission_rate', $data);
        self::assertArrayNotHasKey('revenue_share_rate', $data);
        self::assertArrayNotHasKey('valuation', $data);
        self::assertArrayNotHasKey('duration_months', $data);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): string
    {
        $defaults = [
            'name'              => 'Jean Dupont',
            'email'             => 'jean.dupont@example.com',
            'project_type'      => 'vitrinesite',
            'current_situation' => 'none',
            'scale'             => 'small',
            'partnership_type'  => 'revenue_share',
            'project_stage'     => 'launched',
            'proposal_text'     => 'Je propose un partage de revenus sur mon activité principale.',
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
