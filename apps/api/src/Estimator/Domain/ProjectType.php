<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

enum ProjectType: string
{
    case VitrineSite  = 'vitrinesite';
    case Ecommerce    = 'ecommerce';
    case BusinessApp  = 'businessapp';
    case Refonte      = 'refonte';
    case Other        = 'other';
    case Unknown      = 'unknown';

    public static function fromString(string $value): self
    {
        $case = self::tryFrom($value);
        if ($case === null) {
            throw EstimatorInvariantViolation::invalidEnum('ProjectType', $value);
        }

        return $case;
    }
}
