<?php

declare(strict_types=1);

namespace App\Editorial\Domain;

/**
 * Cycle de vie éditorial d'un article.
 *
 * `Draft`      : brouillon — jamais servi par l'API publique ; `publishedAt` nul.
 * `Published`  : publié — requiert un `publishedAt` non nul ; visible par l'API publique.
 * `Archived`   : retiré du catalogue — non listé, non résolvable par slug.
 *               Conserve la date historique de publication : `publishedAt`
 *               reste non nul si l'article avait été publié auparavant.
 *
 * Invariant à sens unique : `Published` implique `publishedAt` non nul.
 * La réciproque n'est pas imposée (un article archivé garde sa date). Les
 * transitions passent par `Article::publish()` / `Article::archive()`.
 */
enum ArticleStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function isPublished(): bool
    {
        return $this === self::Published;
    }
}
