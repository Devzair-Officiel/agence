<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

enum CurrentSituation: string
{
    case None           = 'none';
    case HasWebsite     = 'has_website';
    case HasEcommerce   = 'has_ecommerce';
    case HasBusinessApp = 'has_business_app';
    case Unknown        = 'unknown';

    public static function fromString(string $value): self
    {
        $case = self::tryFrom($value);
        if ($case === null) {
            throw EstimatorInvariantViolation::invalidEnum('CurrentSituation', $value);
        }

        return $case;
    }
}
