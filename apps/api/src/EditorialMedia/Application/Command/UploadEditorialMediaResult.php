<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Command;

use App\EditorialMedia\Domain\MediaAsset;

/**
 * Résultat renvoyé au contrôleur d'admin à l'issue d'un téléversement réussi.
 * Expose l'agrégat déjà persisté ; le contrôleur en extrait l'UUID pour
 * construire l'URL de prévisualisation et pour journaliser l'événement audit.
 */
final class UploadEditorialMediaResult
{
    public function __construct(public readonly MediaAsset $asset)
    {
    }
}
