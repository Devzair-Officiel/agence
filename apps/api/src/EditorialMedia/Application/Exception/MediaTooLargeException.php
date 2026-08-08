<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Exception;

/**
 * Le fichier téléversé dépasse `MediaUploadPolicy::MAX_SIZE_BYTES`, ou vaut 0.
 * Le contrôleur d'admin la traduit en 413 Payload Too Large.
 */
final class MediaTooLargeException extends \RuntimeException
{
    public static function forSize(int $sizeBytes, int $maxBytes): self
    {
        return new self(\sprintf(
            'Fichier de %d octet(s) refusé. Maximum autorisé : %d octets.',
            $sizeBytes,
            $maxBytes,
        ));
    }
}
