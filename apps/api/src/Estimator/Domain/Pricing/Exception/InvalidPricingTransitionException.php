<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Pricing\Exception;

final class InvalidPricingTransitionException extends \DomainException
{
    public static function notADraft(string $version, string $status): self
    {
        return new self(sprintf(
            'Impossible de publier la configuration "%s" : statut actuel "%s", seul "draft" peut être publié.',
            $version,
            $status,
        ));
    }
}
