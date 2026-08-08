<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Image;

use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;

/**
 * Résultat renvoyé par `ImageProcessorInterface::normalize()`.
 *
 * Représente le fichier réencodé côté serveur : chemin sur disque local,
 * type MIME canonique final, dimensions et hash SHA-256. Le fichier référencé
 * est TEMPORAIRE — le handler applicatif est responsable de le déplacer vers
 * le stockage définitif, puis de le supprimer si la persistance échoue.
 */
final class NormalizedImage
{
    /**
     * @param non-empty-string $temporaryPath chemin absolu vers le fichier réencodé
     */
    public function __construct(
        public readonly string $temporaryPath,
        public readonly MediaType $mimeType,
        public readonly MediaDimensions $dimensions,
        public readonly int $sizeBytes,
        public readonly Sha256 $sha256,
    ) {
    }
}
