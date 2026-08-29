<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

enum VisibilityNeed: string
{
    case SeoBasic           = 'seo_basic';
    case SeoAdvanced        = 'seo_advanced';
    case LocalVisibility    = 'local_visibility';
    case EditorialStrategy  = 'editorial_strategy';

    public static function fromString(string $value): self
    {
        $case = self::tryFrom($value);
        if ($case === null) {
            throw EstimatorInvariantViolation::invalidEnum('VisibilityNeed', $value);
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
                throw EstimatorInvariantViolation::invalidEnum('VisibilityNeed', $value);
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
