<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

final class EstimateLineItem
{
    private function __construct(
        public readonly string $code,
        public readonly EstimateRange $range,
        public readonly LineItemCategory $category,
    ) {}

    public static function create(string $code, EstimateRange $range, LineItemCategory $category): self
    {
        $trimmed = trim($code);

        if ($trimmed === '') {
            throw EstimatorInvariantViolation::invalidLineItem('le code de ligne ne peut pas être vide.');
        }

        return new self($trimmed, $range, $category);
    }

    public function isOneOff(): bool
    {
        return $this->category === LineItemCategory::OneOff;
    }

    public function isRecurring(): bool
    {
        return $this->category === LineItemCategory::Recurring;
    }
}
