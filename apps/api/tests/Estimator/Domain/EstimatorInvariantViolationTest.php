<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use PHPUnit\Framework\TestCase;

final class EstimatorInvariantViolationTest extends TestCase
{
    public function testInvalidEnumHasMessageWithTypeAndValue(): void
    {
        $e = EstimatorInvariantViolation::invalidEnum('ProjectType', 'bad_value');

        self::assertStringContainsString('ProjectType', $e->getMessage());
        self::assertStringContainsString('bad_value', $e->getMessage());
        self::assertInstanceOf(\DomainException::class, $e);
    }

    public function testAllFactoriesReturnInstance(): void
    {
        self::assertInstanceOf(EstimatorInvariantViolation::class, EstimatorInvariantViolation::invalidMoney('raison'));
        self::assertInstanceOf(EstimatorInvariantViolation::class, EstimatorInvariantViolation::invalidRange('raison'));
        self::assertInstanceOf(EstimatorInvariantViolation::class, EstimatorInvariantViolation::invalidVersion('raison'));
        self::assertInstanceOf(EstimatorInvariantViolation::class, EstimatorInvariantViolation::invalidAssumption('raison'));
        self::assertInstanceOf(EstimatorInvariantViolation::class, EstimatorInvariantViolation::invalidLineItem('raison'));
        self::assertInstanceOf(EstimatorInvariantViolation::class, EstimatorInvariantViolation::invalidInput('raison'));
    }
}
