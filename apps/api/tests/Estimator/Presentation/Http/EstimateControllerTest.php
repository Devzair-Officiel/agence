<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Presentation\Http;

use App\Estimator\Infrastructure\Security\EstimateRateLimiter;
use App\Estimator\Presentation\Http\EstimateController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

#[CoversClass(EstimateController::class)]
final class EstimateControllerTest extends WebTestCase
{
    private const ALLOWED_ORIGIN = 'http://localhost:3001';
    private const ENDPOINT       = '/estimate';

    // -------------------------------------------------------------------------
    // HAPPY PATH
    // -------------------------------------------------------------------------

    public function testVitrineSiteReturnsEstimated(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'vitrinesite']),
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame('ok', $data['status']);
        self::assertSame('estimated', $data['outcome']);
        self::assertSame('vitrinesite', $data['recommended_project_type']);
    }

    public function testEcommerceReturnsEstimated(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload([
                'project_type' => 'ecommerce',
                'scale'        => 'medium',
                'features'     => ['payment', 'shipping'],
            ]),
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame('estimated', $data['outcome']);
        self::assertSame('ecommerce', $data['recommended_project_type']);
    }

    public function testBusinessAppReturnsEstimated(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'businessapp']),
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame('estimated', $data['outcome']);
        self::assertSame('businessapp', $data['recommended_project_type']);
    }

    public function testRefonteReturnsEstimated(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'refonte']),
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame('estimated', $data['outcome']);
        self::assertSame('refonte', $data['recommended_project_type']);
    }

    // -------------------------------------------------------------------------
    // INFÉRENCE (Unknown + objectifs)
    // -------------------------------------------------------------------------

    public function testUnknownWithSellOnlineObjectiveInfersEcommerce(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload([
                'project_type' => 'unknown',
                'objectives'   => ['sell_online'],
            ]),
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame('estimated', $data['outcome']);
        self::assertSame('ecommerce', $data['recommended_project_type']);
    }

    public function testUnknownWithPresentBusinessObjectiveInfersVitrinesite(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload([
                'project_type' => 'unknown',
                'objectives'   => ['present_business'],
            ]),
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame('estimated', $data['outcome']);
        self::assertSame('vitrinesite', $data['recommended_project_type']);
    }

    // -------------------------------------------------------------------------
    // HUMAN SCOPING (conflit d'objectifs forts)
    // -------------------------------------------------------------------------

    public function testConflictingStrongObjectivesReturnsHumanScopingRequired(): void
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

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame('human_scoping_required', $data['outcome']);
        self::assertContains('unknown_project_requires_human_scoping', $data['assumptions']);
    }

    public function testExplicitUnknownTypeReturnsHumanScopingRequired(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'unknown']),
        );

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame('human_scoping_required', $data['outcome']);
    }

    // -------------------------------------------------------------------------
    // STRUCTURE DE RÉPONSE
    // -------------------------------------------------------------------------

    public function testResponseContainsNoFloat(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'ecommerce', 'scale' => 'medium']),
        );

        $data = $this->decode($client);
        self::assertIsInt($data['estimate']['minimum']);
        self::assertIsInt($data['estimate']['maximum']);

        foreach ($data['one_off_items'] as $item) {
            self::assertIsInt($item['minimum']);
            self::assertIsInt($item['maximum']);
        }
    }

    public function testPricingVersionIs2026v1(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'vitrinesite']),
        );

        $data = $this->decode($client);
        self::assertSame('2026-v1', $data['pricing_version']);
    }

    public function testRecurringItemsHavePeriodMonth(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload([
                'project_type' => 'vitrinesite',
                'care_needs'   => ['maintenance'],
            ]),
        );

        $data = $this->decode($client);
        self::assertNotEmpty($data['recurring_items']);
        foreach ($data['recurring_items'] as $item) {
            self::assertSame('month', $item['period']);
            self::assertIsInt($item['minimum']);
            self::assertIsInt($item['maximum']);
        }
    }

    public function testAssumptionsAreSnakeCaseCodes(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'refonte', 'scale' => 'small']),
        );

        $data = $this->decode($client);
        foreach ($data['assumptions'] as $assumption) {
            self::assertIsString($assumption);
            self::assertStringNotContainsString(' ', $assumption, 'assumption must be snake_case, not a UI label');
        }
    }

    public function testResponseHeadersAreCorrect(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'vitrinesite']),
        );

        $response = $client->getResponse();
        $requestId = $response->headers->get('X-Request-Id');
        self::assertNotEmpty($requestId);
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $data = $this->decode($client);
        self::assertSame($requestId, $data['request_id']);
    }

    // -------------------------------------------------------------------------
    // VALIDATION
    // -------------------------------------------------------------------------

    public function testUnknownProjectTypeReturns400(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload(['project_type' => 'not_a_type']),
        );

        self::assertResponseStatusCodeSame(400);
        $data = $this->decode($client);
        self::assertSame('validation_error', $data['code']);
        self::assertArrayHasKey('project_type', $data['errors']);
    }

    public function testUnknownFeatureReturns400(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: $this->payload([
                'project_type' => 'ecommerce',
                'features'     => ['blockchain_nft'],
            ]),
        );

        self::assertResponseStatusCodeSame(400);
        $data = $this->decode($client);
        self::assertSame('validation_error', $data['code']);
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
        $data = $this->decode($client);
        self::assertSame('invalid_json', $data['code']);
    }

    public function testPayloadTooLargeReturns413(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: str_repeat('a', 10_001),
        );

        self::assertResponseStatusCodeSame(413);
        $data = $this->decode($client);
        self::assertSame('payload_too_large', $data['code']);
    }

    public function testMissingRequiredFieldsReturns400(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: $this->serverHeaders(),
            content: '{}',
        );

        self::assertResponseStatusCodeSame(400);
        $data = $this->decode($client);
        self::assertSame('validation_error', $data['code']);
        self::assertArrayHasKey('project_type', $data['errors']);
    }

    // -------------------------------------------------------------------------
    // SÉCURITÉ — Origin / Rate limit
    // -------------------------------------------------------------------------

    public function testMissingOriginIsForbidden(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            self::ENDPOINT,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: $this->payload(['project_type' => 'vitrinesite']),
        );

        self::assertResponseStatusCodeSame(403);
        $data = $this->decode($client);
        self::assertSame('origin_not_allowed', $data['code']);
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
            content: $this->payload(['project_type' => 'vitrinesite']),
        );

        self::assertResponseStatusCodeSame(403);
        $data = $this->decode($client);
        self::assertSame('origin_not_allowed', $data['code']);
    }

    public function testRateLimitExhausionReturns429WithRetryAfter(): void
    {
        $client = self::createClient();

        // The kernel browser reboots between requests by default, which would
        // reset the InMemoryStorage on each request. disableReboot() keeps the
        // same kernel (and the same storage instance) across all request() calls.
        $client->disableReboot();

        // Install a tight rate limiter (limit=1) directly in the container so
        // the second request exhausts the bucket, regardless of .env.test settings.
        $factory = new RateLimiterFactory(
            ['policy' => 'token_bucket', 'id' => 'estimate_ip_test', 'limit' => 1, 'rate' => ['interval' => '1 hour', 'amount' => 1]],
            new InMemoryStorage(),
        );
        self::getContainer()->set(EstimateRateLimiter::class, new EstimateRateLimiter($factory));

        $headers = $this->serverHeaders();
        $payload = $this->payload(['project_type' => 'vitrinesite']);

        // First request: consumes the single token → succeeds.
        $client->request('POST', self::ENDPOINT, server: $headers, content: $payload);
        self::assertResponseStatusCodeSame(200);

        // Second request: bucket empty → 429 + Retry-After.
        $client->request('POST', self::ENDPOINT, server: $headers, content: $payload);
        self::assertResponseStatusCodeSame(429);
        $data = $this->decode($client);
        self::assertSame('rate_limited', $data['code']);
        self::assertNotEmpty($client->getResponse()->headers->get('Retry-After'));
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $overrides
     */
    private function payload(array $overrides = []): string
    {
        $defaults = [
            'project_type'      => 'vitrinesite',
            'current_situation' => 'none',
            'scale'             => 'small',
        ];

        return json_encode(array_merge($defaults, $overrides), \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, string>
     */
    private function serverHeaders(): array
    {
        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ORIGIN'  => self::ALLOWED_ORIGIN,
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
}
