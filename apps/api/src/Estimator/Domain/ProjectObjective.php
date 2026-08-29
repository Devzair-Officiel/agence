<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

enum ProjectObjective: string
{
    case PresentBusiness      = 'present_business';
    case SellOnline           = 'sell_online';
    case GenerateLeads        = 'generate_leads';
    case DigitalizeProcess    = 'digitalize_process';
    case ImproveExisting      = 'improve_existing';
    case ImproveVisibility    = 'improve_visibility';

    public static function fromString(string $value): self
    {
        $case = self::tryFrom($value);
        if ($case === null) {
            throw EstimatorInvariantViolation::invalidEnum('ProjectObjective', $value);
        }

        return $case;
    }

    /** @param list<string> $values
     *  @return list<self>
     */
    public static function fromList(array $values): array
    {
        $result = [];
        $seen   = [];

        foreach ($values as $value) {
            $case = self::tryFrom($value);
            if ($case === null) {
                throw EstimatorInvariantViolation::invalidEnum('ProjectObjective', $value);
            }
            if (!isset($seen[$case->value])) {
                $seen[$case->value] = true;
                $result[]           = $case;
            }
        }

        return $result;
    }

    /** @param list<self> $objectives
     *  @return list<string>
     */
    public static function toList(array $objectives): array
    {
        return array_map(static fn(self $o): string => $o->value, $objectives);
    }
}
