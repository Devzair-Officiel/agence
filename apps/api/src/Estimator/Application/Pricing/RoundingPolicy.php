<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

final class RoundingPolicy
{
    private const STEP_MINOR = 5000; // 50 EUR in cents

    /**
     * Rounds MIN down to the nearest 50 EUR.
     * Never artificially reduces the floor of the estimate.
     */
    public static function roundMinConservative(int $amountMinor): int
    {
        return (int)(floor($amountMinor / self::STEP_MINOR) * self::STEP_MINOR);
    }

    /**
     * Rounds MAX up to the nearest 50 EUR.
     * Never artificially caps the ceiling of the estimate.
     */
    public static function roundMaxConservative(int $amountMinor): int
    {
        return (int)(ceil($amountMinor / self::STEP_MINOR) * self::STEP_MINOR);
    }
}
