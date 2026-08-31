<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Pricing\Exception;

final class DuplicatePricingVersionException extends \DomainException
{
    public static function versionAlreadyExists(string $version): self
    {
        return new self(sprintf('Une configuration tarifaire avec la version "%s" existe déjà.', $version));
    }
}
