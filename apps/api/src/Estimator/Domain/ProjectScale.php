<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

enum ProjectScale: string
{
    case Small   = 'small';
    case Medium  = 'medium';
    case Large   = 'large';
    case Unknown = 'unknown';

    public static function fromString(string $value): self
    {
        $case = self::tryFrom($value);
        if ($case === null) {
            throw EstimatorInvariantViolation::invalidEnum('ProjectScale', $value);
        }

        return $case;
    }
}
