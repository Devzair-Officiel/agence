<?php

declare(strict_types=1);

namespace App\Tests\Contact\Security;

use App\Contact\Security\CloudflareTurnstileVerifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(CloudflareTurnstileVerifier::class)]
final class CloudflareTurnstileVerifierTest extends TestCase
{
    public function testMissingTokenIsRejectedWithoutHttpCall(): void
    {
        $client = new MockHttpClient(function (): MockResponse {
            self::fail('Aucun appel HTTP ne doit être fait pour un token vide.');
        });

        $verifier = new CloudflareTurnstileVerifier('secret', $client, new NullLogger());

        $verdict = $verifier->verify(null, '203.0.113.7');

        self::assertFalse($verdict->success);
        self::assertSame('missing_token', $verdict->reason);
    }

    public function testSuccessfulResponseAccepts(): void
    {
        $client = new MockHttpClient(new MockResponse(
            json_encode(['success' => true], \JSON_THROW_ON_ERROR),
            ['response_headers' => ['content-type: application/json']],
        ));

        $verifier = new CloudflareTurnstileVerifier('secret', $client, new NullLogger());

        $verdict = $verifier->verify('valid-token', '203.0.113.7');

        self::assertTrue($verdict->success);
    }

    public function testRejectedResponseIsRejected(): void
    {
        $client = new MockHttpClient(new MockResponse(
            json_encode(['success' => false, 'error-codes' => ['invalid-input-response']], \JSON_THROW_ON_ERROR),
            ['response_headers' => ['content-type: application/json']],
        ));

        $verifier = new CloudflareTurnstileVerifier('secret', $client, new NullLogger());

        $verdict = $verifier->verify('bad-token', '203.0.113.7');

        self::assertFalse($verdict->success);
        self::assertSame('rejected', $verdict->reason);
    }

    public function testNon200IsRejected(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['http_code' => 502]));

        $verifier = new CloudflareTurnstileVerifier('secret', $client, new NullLogger());

        $verdict = $verifier->verify('some-token', '203.0.113.7');

        self::assertFalse($verdict->success);
        self::assertSame('http_error', $verdict->reason);
    }
}
