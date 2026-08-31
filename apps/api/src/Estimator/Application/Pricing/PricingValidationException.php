<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

final class PricingValidationException extends \DomainException
{
    /** @var list<string> */
    private array $errors;

    /**
     * @param list<string> $errors
     */
    public static function invalidConfiguration(string $version, array $errors): self
    {
        $exception         = new self(sprintf(
            'La configuration "%s" est incomplète et ne peut pas être publiée (%d erreur(s)).',
            $version,
            count($errors),
        ));
        $exception->errors = $errors;

        return $exception;
    }

    /** @return list<string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
