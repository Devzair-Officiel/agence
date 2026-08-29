<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

final class EstimateRange
{
    private function __construct(
        public readonly Money $minimum,
        public readonly Money $maximum,
    ) {}

    public static function create(Money $minimum, Money $maximum): self
    {
        if (!$minimum->isSameCurrency($maximum)) {
            throw EstimatorInvariantViolation::invalidRange(
                sprintf('minimum (%s) et maximum (%s) doivent être dans la même devise.', $minimum->currency, $maximum->currency)
            );
        }

        if ($minimum->amountMinor > $maximum->amountMinor) {
            throw EstimatorInvariantViolation::invalidRange(
                sprintf('minimum (%d) ne peut pas être supérieur au maximum (%d).', $minimum->amountMinor, $maximum->amountMinor)
            );
        }

        return new self($minimum, $maximum);
    }

    public function currency(): string
    {
        return $this->minimum->currency;
    }

    public function equals(self $other): bool
    {
        return $this->minimum->equals($other->minimum)
            && $this->maximum->equals($other->maximum);
    }
}
