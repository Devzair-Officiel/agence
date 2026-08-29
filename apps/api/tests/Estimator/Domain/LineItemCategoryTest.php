<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\LineItemCategory;
use PHPUnit\Framework\TestCase;

final class LineItemCategoryTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('validValueProvider')]
    public function testFromStringAcceptsValidValue(string $value, LineItemCategory $expected): void
    {
        self::assertSame($expected, LineItemCategory::fromString($value));
    }

    public static function validValueProvider(): iterable
    {
        yield 'one_off'    => ['one_off', LineItemCategory::OneOff];
        yield 'recurring'  => ['recurring', LineItemCategory::Recurring];
    }

    public function testFromStringRejectsInvalidValue(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        LineItemCategory::fromString('monthly');
    }
}
