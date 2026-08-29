<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\ProjectScale;
use PHPUnit\Framework\TestCase;

final class ProjectScaleTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('validValueProvider')]
    public function testFromStringAcceptsValidValue(string $value, ProjectScale $expected): void
    {
        self::assertSame($expected, ProjectScale::fromString($value));
    }

    public static function validValueProvider(): iterable
    {
        yield 'small'   => ['small', ProjectScale::Small];
        yield 'medium'  => ['medium', ProjectScale::Medium];
        yield 'large'   => ['large', ProjectScale::Large];
        yield 'unknown' => ['unknown', ProjectScale::Unknown];
    }

    public function testFromStringRejectsInvalidValue(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        ProjectScale::fromString('xl');
    }
}
