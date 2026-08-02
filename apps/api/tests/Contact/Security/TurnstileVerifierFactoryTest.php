<?php

declare(strict_types=1);

namespace App\Tests\Contact\Security;

use App\Contact\Security\AlwaysAllowTurnstileVerifier;
use App\Contact\Security\CloudflareTurnstileVerifier;
use App\Contact\Security\TurnstileVerifierFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;

#[CoversClass(TurnstileVerifierFactory::class)]
final class TurnstileVerifierFactoryTest extends TestCase
{
    public function testDisabledReturnsAllowAll(): void
    {
        $factory = new TurnstileVerifierFactory(
            enabled: false,
            secret: null,
            httpClient: new MockHttpClient(),
            logger: new NullLogger(),
        );

        self::assertInstanceOf(AlwaysAllowTurnstileVerifier::class, $factory->create());
    }

    public function testEnabledWithSecretReturnsCloudflare(): void
    {
        $factory = new TurnstileVerifierFactory(
            enabled: true,
            secret: 'a-secret',
            httpClient: new MockHttpClient(),
            logger: new NullLogger(),
        );

        self::assertInstanceOf(CloudflareTurnstileVerifier::class, $factory->create());
    }

    public function testEnabledWithoutSecretThrows(): void
    {
        $factory = new TurnstileVerifierFactory(
            enabled: true,
            secret: '',
            httpClient: new MockHttpClient(),
            logger: new NullLogger(),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('TURNSTILE_SECRET');

        $factory->create();
    }
}
