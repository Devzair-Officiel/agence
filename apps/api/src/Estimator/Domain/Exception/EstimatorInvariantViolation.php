<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Exception;

final class EstimatorInvariantViolation extends \DomainException
{
    public static function invalidEnum(string $type, string $value): self
    {
        return new self(sprintf('Valeur inconnue pour "%s" : "%s".', $type, $value));
    }

    public static function invalidMoney(string $reason): self
    {
        return new self(sprintf('Montant invalide : %s', $reason));
    }

    public static function invalidRange(string $reason): self
    {
        return new self(sprintf('Fourchette invalide : %s', $reason));
    }

    public static function invalidVersion(string $reason): self
    {
        return new self(sprintf('Version tarifaire invalide : %s', $reason));
    }

    public static function invalidAssumption(string $reason): self
    {
        return new self(sprintf('Hypothèse invalide : %s', $reason));
    }

    public static function invalidLineItem(string $reason): self
    {
        return new self(sprintf('Ligne de devis invalide : %s', $reason));
    }

    public static function invalidInput(string $reason): self
    {
        return new self(sprintf('Données d\'entrée invalides : %s', $reason));
    }
}
