<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Command;

/**
 * DTO d'entrée du use case `UploadEditorialMediaHandler`.
 *
 * Le contrôleur d'admin ne fait QUE traduire un `UploadedFile` Symfony vers
 * ce DTO — il n'inspecte ni les magic bytes, ni les dimensions, ni le hash.
 * Toute la logique métier appartient au handler, ce qui garantit qu'un même
 * pipeline s'appliquera qu'on téléverse depuis HTTP, depuis une commande CLI
 * (future 9B), ou depuis un import batch.
 *
 * `$sourcePath` doit pointer vers un fichier accessible en lecture par le
 * processus PHP au moment du dispatch — le handler ne le déplace pas, il en
 * lit le contenu et laisse le nettoyage à l'appelant HTTP.
 */
final class UploadEditorialMedia
{
    /**
     * @param non-empty-string $sourcePath      chemin absolu du fichier téléversé (tmpfile)
     * @param non-empty-string $originalFilename nom d'origine côté client, purement métadonnée
     */
    public function __construct(
        public readonly string $sourcePath,
        public readonly string $originalFilename,
    ) {
    }
}
