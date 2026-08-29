<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

final class Money
{
    private function __construct(
        public readonly int $amountMinor,
        public readonly string $currency,
    ) {}

    public static function of(int $amountMinor, string $currency): self
    {
        if ($amountMinor < 0) {
            throw EstimatorInvariantViolation::invalidMoney('le montant ne peut pas être négatif.');
        }

        $currency = strtoupper(trim($currency));

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw EstimatorInvariantViolation::invalidMoney(sprintf('la devise "%s" n\'est pas un code devise à 3 lettres valide.', $currency));
        }

        return new self($amountMinor, $currency);
    }

    public function isSameCurrency(self $other): bool
    {
        return $this->currency === $other->currency;
    }

    public function equals(self $other): bool
    {
        return $this->amountMinor === $other->amountMinor
            && $this->currency === $other->currency;
    }
}
