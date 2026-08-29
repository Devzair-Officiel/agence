<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

enum CareNeed: string
{
    case Maintenance          = 'maintenance';
    case ContentUpdates       = 'content_updates';
    case ContinuousSeo        = 'continuous_seo';
    case Support              = 'support';
    case FunctionalEvolution  = 'functional_evolution';

    public static function fromString(string $value): self
    {
        $case = self::tryFrom($value);
        if ($case === null) {
            throw EstimatorInvariantViolation::invalidEnum('CareNeed', $value);
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
                throw EstimatorInvariantViolation::invalidEnum('CareNeed', $value);
            }
            if (!isset($seen[$case->value])) {
                $seen[$case->value] = true;
                $result[]           = $case;
            }
        }

        return $result;
    }

    /** @param list<self> $needs
     *  @return list<string>
     */
    public static function toList(array $needs): array
    {
        return array_map(static fn(self $n): string => $n->value, $needs);
    }
}
