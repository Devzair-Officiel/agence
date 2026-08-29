<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\ProjectType;
use PHPUnit\Framework\TestCase;

final class ProjectTypeTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('validValueProvider')]
    public function testFromStringAcceptsValidValue(string $value, ProjectType $expected): void
    {
        self::assertSame($expected, ProjectType::fromString($value));
    }

    public static function validValueProvider(): iterable
    {
        yield 'vitrinesite' => ['vitrinesite', ProjectType::VitrineSite];
        yield 'ecommerce'   => ['ecommerce', ProjectType::Ecommerce];
        yield 'businessapp' => ['businessapp', ProjectType::BusinessApp];
        yield 'refonte'     => ['refonte', ProjectType::Refonte];
        yield 'other'       => ['other', ProjectType::Other];
        yield 'unknown'     => ['unknown', ProjectType::Unknown];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidValueProvider')]
    public function testFromStringRejectsInvalidValue(string $value): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        ProjectType::fromString($value);
    }

    public static function invalidValueProvider(): iterable
    {
        yield 'empty'         => [''];
        yield 'unknown_value' => ['freelance'];
        yield 'uppercase'     => ['ECOMMERCE'];
    }
}
