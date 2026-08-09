<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\Article;

/**
 * Résultat d'un `SetDraftArticleHeroImageHandler`.
 *
 * `mutated` distingue :
 *   - `true` : l'image ou l'alt a réellement changé — `updatedAt` a
 *     progressé et le flush a été effectué ;
 *   - `false` : la valeur soumise est équivalente à la valeur courante
 *     (même mediaId + même alt) — aucun flush, `updatedAt` inchangé.
 *
 * La couche présentation utilise `mutated` pour choisir entre un flash
 * `success` (mutation) et `info` (no-op) dans le PRG.
 */
final class SetDraftArticleHeroImageResult
{
    private function __construct(
        public readonly Article $article,
        public readonly bool $mutated,
    ) {
    }

    public static function mutated(Article $article): self
    {
        return new self($article, true);
    }

    public static function unchanged(Article $article): self
    {
        return new self($article, false);
    }
}
