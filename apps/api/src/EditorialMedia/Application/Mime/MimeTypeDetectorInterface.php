<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Mime;

use App\EditorialMedia\Application\Exception\InvalidImageException;

/**
 * Port sortant : détection du type MIME réel d'un fichier à partir de son
 * contenu (jamais de l'extension). Une seule implémentation en 9A :
 * `FinfoMimeTypeDetector` (extension `fileinfo`, `FILEINFO_MIME_TYPE`).
 *
 * Isolé en port pour permettre à la couche Application de rester testable sans
 * dépendre de `finfo` — les tests unitaires du handler injectent un stub.
 */
interface MimeTypeDetectorInterface
{
    /**
     * @param non-empty-string $sourcePath
     *
     * @throws InvalidImageException lorsque le fichier est illisible ou vide
     */
    public function detect(string $sourcePath): string;
}
