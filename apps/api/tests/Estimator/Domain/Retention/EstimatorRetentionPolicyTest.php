<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain\Retention;

use App\Estimator\Domain\Retention\EstimatorRetentionPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EstimatorRetentionPolicy::class)]
final class EstimatorRetentionPolicyTest extends TestCase
{
    public function testMonthsConstantIs24(): void
    {
        self::assertSame(24, EstimatorRetentionPolicy::MONTHS);
    }

    public function testCutoffIs24MonthsBeforeNow(): void
    {
        $now    = new \DateTimeImmutable('2026-09-01T00:00:00+00:00');
        $cutoff = EstimatorRetentionPolicy::cutoff($now);

        self::assertSame('2024-09-01T00:00:00+00:00', $cutoff->format(\DateTimeInterface::ATOM));
    }

    public function testEntityCreatedExactlyAtCutoffIsNotExpired(): void
    {
        $now    = new \DateTimeImmutable('2026-09-01T12:00:00+00:00');
        $cutoff = EstimatorRetentionPolicy::cutoff($now);

        // createdAt = cutoff → NOT < cutoff → NOT expired
        self::assertFalse($cutoff < $cutoff);
    }

    public function testEntityCreatedOneSecondBeforeCutoffIsExpired(): void
    {
        $now    = new \DateTimeImmutable('2026-09-01T12:00:00+00:00');
        $cutoff = EstimatorRetentionPolicy::cutoff($now);

        $createdAt = $cutoff->modify('-1 second');
        self::assertTrue($createdAt < $cutoff);
    }

    public function testEntityCreatedOneSecondAfterCutoffIsNotExpired(): void
    {
        $now    = new \DateTimeImmutable('2026-09-01T12:00:00+00:00');
        $cutoff = EstimatorRetentionPolicy::cutoff($now);

        $createdAt = $cutoff->modify('+1 second');
        self::assertFalse($createdAt < $cutoff);
    }
}
