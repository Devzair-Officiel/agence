<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

final class EstimateAssumption implements \Stringable
{
    private function __construct(private readonly string $code) {}

    public static function fromCode(string $code): self
    {
        $trimmed = trim($code);

        if ($trimmed === '') {
            throw EstimatorInvariantViolation::invalidAssumption('le code d\'hypothèse ne peut pas être vide.');
        }

        return new self($trimmed);
    }

    public function code(): string
    {
        return $this->code;
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
