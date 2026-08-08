<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Support;

use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Application\Mime\MimeTypeDetectorInterface;

/**
 * Détecteur MIME configurable pour les tests du handler.
 *
 * Un mapping `chemin → mime` est fourni au constructeur ; à défaut, un MIME
 * par défaut est renvoyé — utile pour les scénarios nominaux.
 */
final class FakeMimeTypeDetector implements MimeTypeDetectorInterface
{
    /**
     * @param array<string, string> $mapping
     */
    public function __construct(
        private readonly string $defaultMime = 'image/jpeg',
        private readonly array $mapping = [],
        private readonly bool $throwUnreadable = false,
    ) {
    }

    public function detect(string $sourcePath): string
    {
        if ($this->throwUnreadable) {
            throw new InvalidImageException('Fichier illisible.');
        }

        return $this->mapping[$sourcePath] ?? $this->defaultMime;
    }
}
