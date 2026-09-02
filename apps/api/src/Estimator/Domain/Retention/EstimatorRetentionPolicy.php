<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Retention;

/**
 * Source de vérité unique de la politique de conservation des données estimateur.
 *
 * Décision métier V1 : 24 mois depuis createdAt, sans prolongation
 * sur activité, modification technique ou version tarifaire.
 *
 * Utilisation : EstimatorRetentionPolicy::cutoff($now) retourne le seuil
 * avant lequel une entité est considérée expirée.
 * Règle : createdAt < cutoff → expiré (borne strictement inférieure).
 */
final class EstimatorRetentionPolicy
{
    public const MONTHS = 24;

    public static function cutoff(\DateTimeImmutable $now): \DateTimeImmutable
    {
        return $now->modify(\sprintf('-%d months', self::MONTHS));
    }
}
