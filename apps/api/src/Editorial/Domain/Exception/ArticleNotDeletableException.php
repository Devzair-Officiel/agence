<?php

declare(strict_types=1);

namespace App\Editorial\Domain\Exception;

use App\Editorial\Domain\ArticleStatus;

/**
 * Levée quand une suppression définitive est demandée sur un article
 * qui n'est pas en statut `Archived`.
 *
 * Seul un article archivé peut être supprimé définitivement — règle métier
 * garantie par le handler (jamais contournée côté HTTP). Traduite en 409
 * par la couche de présentation.
 */
final class ArticleNotDeletableException extends \DomainException
{
    public static function becauseNotArchived(ArticleStatus $status): self
    {
        return new self(\sprintf(
            'Un article ne peut être supprimé définitivement que s\'il est archivé (actuel : "%s").',
            $status->value,
        ));
    }
}
