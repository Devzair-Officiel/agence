<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

enum LineItemCategory: string
{
    case OneOff    = 'one_off';
    case Recurring = 'recurring';

    public static function fromString(string $value): self
    {
        $case = self::tryFrom($value);
        if ($case === null) {
            throw EstimatorInvariantViolation::invalidEnum('LineItemCategory', $value);
        }

        return $case;
    }
}
