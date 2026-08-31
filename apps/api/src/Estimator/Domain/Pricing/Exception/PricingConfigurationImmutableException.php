<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Pricing\Exception;

final class PricingConfigurationImmutableException extends \DomainException
{
    public static function cannotModify(string $version, string $status): self
    {
        return new self(sprintf(
            'La configuration tarifaire "%s" (statut : %s) est immuable et ne peut plus être modifiée.',
            $version,
            $status,
        ));
    }

    public static function cannotDelete(string $version, string $status): self
    {
        return new self(sprintf(
            'La configuration tarifaire "%s" (statut : %s) ne peut pas être supprimée.',
            $version,
            $status,
        ));
    }
}
