<?php

declare(strict_types=1);

namespace App\Editorial\Application\Media;

/**
 * Résultat renvoyé par `PublicMediaStreamerInterface` : un handle de
 * ressource ouverte + les métadonnées nécessaires à la réponse HTTP publique
 * (Content-Type / Content-Length / ETag / Last-Modified).
 *
 * Le port ne fuite rien de spécifique à `EditorialMedia` :
 *   - `stream` est une simple `resource` PHP (interface commune) que
 *     l'appelant refermera après lecture ;
 *   - `mimeType` est déjà normalisé en chaîne côté serveur (`MediaType::mime()`) ;
 *   - `sha256` sert à composer un ETag fort stable côté route publique.
 *
 * @internal Consommé exclusivement par le contrôleur public média.
 */
final class PublicMediaResource
{
    /**
     * @param resource $stream
     */
    public function __construct(
        public readonly mixed $stream,
        public readonly string $mimeType,
        public readonly int $sizeBytes,
        public readonly string $sha256,
        public readonly \DateTimeImmutable $lastModified,
    ) {
    }
}
