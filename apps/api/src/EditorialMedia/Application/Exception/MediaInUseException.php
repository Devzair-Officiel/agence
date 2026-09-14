<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Exception;

/**
 * Levée par `DeleteEditorialMediaHandler` quand le média est encore référencé
 * par au moins un article (tous statuts : draft, published, archived).
 *
 * Le contrôleur traduit cette exception en réponse HTTP 409 avec le code
 * machine `media_in_use`.
 */
final class MediaInUseException extends \RuntimeException
{
    public static function referencedByArticle(string $assetId): self
    {
        return new self(\sprintf(
            'Le média %s est référencé par au moins un article et ne peut pas être supprimé.',
            $assetId,
        ));
    }
}
