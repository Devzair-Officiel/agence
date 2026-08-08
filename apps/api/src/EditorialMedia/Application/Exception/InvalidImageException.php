<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Exception;

/**
 * Le fichier a passé le contrôle MIME mais ne peut pas être décodé comme une
 * image raster valide (données corrompues, MIME contrefait, format sous-jacent
 * non supporté par GD…). Le contrôleur d'admin la traduit en 422 Unprocessable
 * Entity.
 *
 * Aucun détail bas-niveau de GD n'est propagé côté message pour éviter toute
 * fuite d'information sur l'implémentation.
 */
final class InvalidImageException extends \RuntimeException
{
    public static function undecodable(): self
    {
        return new self('Le fichier fourni ne peut pas être décodé comme une image valide.');
    }
}
