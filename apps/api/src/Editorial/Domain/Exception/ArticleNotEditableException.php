<?php

declare(strict_types=1);

namespace App\Editorial\Domain\Exception;

use App\Editorial\Domain\ArticleStatus;

/**
 * Levée quand une mutation éditoriale (titre, résumé, corps, SEO, auteur,
 * piliers) est demandée sur un article qui n'est pas en statut `Draft`.
 *
 * L'édition d'un article publié ou archivé est interdite dans le domaine :
 * la seule voie consiste à archiver puis restaurer l'article vers l'état
 * `Draft` avant toute modification. Traduite en 409 par la couche HTTP.
 */
final class ArticleNotEditableException extends \DomainException
{
    public static function becauseStatusIs(ArticleStatus $status): self
    {
        return new self(\sprintf(
            'Un article ne peut être modifié qu\'en statut brouillon (actuel : "%s").',
            $status->value,
        ));
    }
}
