<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Exception;

/**
 * Une opération de stockage a échoué (impossibilité d'écrire le fichier
 * normalisé, collision de storage key, indisponibilité du répertoire cible…).
 * Le contrôleur d'admin la traduit en 500 Internal Server Error et l'audit
 * logger consigne l'incident.
 *
 * Aucun chemin absolu, aucun détail système ne doit être propagé côté message
 * — l'appelant applicatif s'en tient à un vocabulaire de haut niveau ; le
 * détail brut est journalisé côté Infrastructure.
 */
final class MediaStorageException extends \RuntimeException
{
    public static function writeFailed(): self
    {
        return new self('Écriture du média impossible sur le stockage privé.');
    }

    public static function readFailed(): self
    {
        return new self('Lecture du média impossible sur le stockage privé.');
    }
}
