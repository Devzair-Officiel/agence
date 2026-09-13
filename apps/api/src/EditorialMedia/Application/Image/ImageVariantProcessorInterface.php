<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Image;

use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Domain\MediaType;

/**
 * Port applicatif : génère les deux variants WebP (card + hero) à partir
 * du fichier normalisé produit par `ImageProcessorInterface` (Phase 9C).
 *
 * L'implémentation charge l'image source une seule fois, génère card puis
 * hero, libère chaque bitmap immédiatement après encodage pour minimiser
 * l'empreinte mémoire.
 *
 * @throws InvalidImageException si la source est trop petite ou illisible
 */
interface ImageVariantProcessorInterface
{
    /**
     * @param non-empty-string $normalizedPath chemin vers le fichier normalisé
     *
     * @throws InvalidImageException
     */
    public function generateVariants(string $normalizedPath, MediaType $type): ImageVariantsResult;
}
