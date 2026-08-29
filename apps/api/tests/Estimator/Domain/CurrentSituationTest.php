<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\CurrentSituation;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use PHPUnit\Framework\TestCase;

final class CurrentSituationTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('validValueProvider')]
    public function testFromStringAcceptsValidValue(string $value, CurrentSituation $expected): void
    {
        self::assertSame($expected, CurrentSituation::fromString($value));
    }

    public static function validValueProvider(): iterable
    {
        yield 'none'             => ['none', CurrentSituation::None];
        yield 'has_website'      => ['has_website', CurrentSituation::HasWebsite];
        yield 'has_ecommerce'    => ['has_ecommerce', CurrentSituation::HasEcommerce];
        yield 'has_business_app' => ['has_business_app', CurrentSituation::HasBusinessApp];
        yield 'unknown'          => ['unknown', CurrentSituation::Unknown];
    }

    public function testFromStringRejectsInvalidValue(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        CurrentSituation::fromString('has_nothing');
    }
}
