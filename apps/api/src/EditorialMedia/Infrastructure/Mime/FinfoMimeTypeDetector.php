<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Mime;

use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Application\Mime\MimeTypeDetectorInterface;

/**
 * Détection MIME côté serveur via l'extension `fileinfo` (magic bytes).
 * Utilisation stricte de `FILEINFO_MIME_TYPE` — pas d'`enconding`, pas de
 * `symlink follow`, pas de fallback sur l'extension du chemin.
 *
 * Aucune donnée de l'utilisateur (nom, extension) n'influe sur le résultat :
 * seul le contenu du fichier compte.
 */
final class FinfoMimeTypeDetector implements MimeTypeDetectorInterface
{
    public function detect(string $sourcePath): string
    {
        if (!is_file($sourcePath) || !is_readable($sourcePath)) {
            throw InvalidImageException::undecodable();
        }

        $finfo = new \finfo(\FILEINFO_MIME_TYPE);
        $mime = $finfo->file($sourcePath);

        if ($mime === false || $mime === '') {
            throw InvalidImageException::undecodable();
        }

        return strtolower($mime);
    }
}
