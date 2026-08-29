<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\PricingVersion;
use PHPUnit\Framework\TestCase;

final class PricingVersionTest extends TestCase
{
    public function testCreatesFromString(): void
    {
        $v = PricingVersion::fromString('v1.0');

        self::assertSame('v1.0', $v->value());
        self::assertSame('v1.0', (string) $v);
    }

    public function testTrimsWhitespace(): void
    {
        $v = PricingVersion::fromString('  v2.0  ');

        self::assertSame('v2.0', $v->value());
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        PricingVersion::fromString('');
    }

    public function testRejectsWhitespaceOnlyString(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        PricingVersion::fromString('   ');
    }

    public function testEquals(): void
    {
        $v1 = PricingVersion::fromString('v1.0');
        $v2 = PricingVersion::fromString('v1.0');
        $v3 = PricingVersion::fromString('v2.0');

        self::assertTrue($v1->equals($v2));
        self::assertFalse($v1->equals($v3));
    }

    public function testImplementsStringable(): void
    {
        $v = PricingVersion::fromString('test-2026-08');

        self::assertInstanceOf(\Stringable::class, $v);
    }
}
