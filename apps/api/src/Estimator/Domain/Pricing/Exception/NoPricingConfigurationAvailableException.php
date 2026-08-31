<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Pricing\Exception;

final class NoPricingConfigurationAvailableException extends \RuntimeException
{
    public static function noPublishedConfiguration(): self
    {
        return new self('Aucune configuration tarifaire publiée disponible. Le moteur ne peut pas calculer d\'estimation.');
    }
}
