<?php

declare(strict_types=1);

namespace App\Admin\Application;

use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\Exception\AdminUserInvariantViolation;

/**
 * Politique de mot de passe minimale pour les comptes administrateurs.
 *
 * Vérifie uniquement des règles factuelles et déterministes :
 * - longueur minimale (12 caractères, alignée sur les recommandations
 *   ANSSI/NIST pour un compte à privilèges avec MFA différée) ;
 * - refus des chaînes composées uniquement d'espaces ;
 * - refus d'un mot de passe strictement égal à l'email (normalisé).
 *
 * Pas de règle « complexité » (majuscule/chiffre/spécial) : ces règles sont
 * contre-productives sans réduire l'espace des mots de passe utiles. La
 * défense reste (a) longueur suffisante, (b) throttling firewall, (c) hasher
 * Argon2id.
 */
final class PasswordPolicy
{
    public const MIN_LENGTH = 12;

    public static function assert(string $plainPassword, AdminEmail $email): void
    {
        if ($plainPassword === '') {
            throw new AdminUserInvariantViolation('Le mot de passe ne peut pas être vide.');
        }

        if (trim($plainPassword) === '') {
            throw new AdminUserInvariantViolation('Le mot de passe ne peut pas être composé uniquement d\'espaces.');
        }

        if (mb_strlen($plainPassword) < self::MIN_LENGTH) {
            throw new AdminUserInvariantViolation(\sprintf(
                'Le mot de passe doit contenir au moins %d caractères.',
                self::MIN_LENGTH,
            ));
        }

        if (mb_strtolower($plainPassword) === $email->normalized()) {
            throw new AdminUserInvariantViolation('Le mot de passe ne peut pas être identique à l\'email.');
        }
    }
}
