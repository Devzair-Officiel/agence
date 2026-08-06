<?php

declare(strict_types=1);

namespace App\Editorial\Domain\Exception;

use App\Editorial\Domain\ArticleStatus;

/**
 * Levée quand une transition de cycle de vie est refusée par le domaine :
 * par exemple une tentative de restauration (`Article::restore()`) sur un
 * article encore publié — la restauration exige un article archivé.
 * Traduite en 409 par la couche HTTP.
 */
final class InvalidArticleTransitionException extends \DomainException
{
    public static function cannotRestoreFrom(ArticleStatus $status): self
    {
        return new self(\sprintf(
            'Un article ne peut être restauré que depuis le statut "archived" (actuel : "%s").',
            $status->value,
        ));
    }
}
