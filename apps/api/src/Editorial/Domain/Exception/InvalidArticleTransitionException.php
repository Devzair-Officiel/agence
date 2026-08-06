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

    /**
     * Refus de publication depuis un statut autre que `Draft` — utilisé par
     * `PublishDraftArticleHandler` pour distinguer :
     *   - `Archived` : bloqué net (la publication passe par une restauration
     *     explicite, cf. directive utilisateur 8C3 n°3) ;
     *   - `Published` : bloqué net également, la publication n'est jamais
     *     idempotente dans le chemin admin (transparence explicite plutôt
     *     que no-op silencieux).
     */
    public static function cannotPublishFrom(ArticleStatus $status): self
    {
        return new self(\sprintf(
            'Un brouillon ne peut être publié qu\'à partir du statut "draft" (actuel : "%s").',
            $status->value,
        ));
    }
}
