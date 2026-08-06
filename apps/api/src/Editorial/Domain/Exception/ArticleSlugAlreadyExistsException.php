<?php

declare(strict_types=1);

namespace App\Editorial\Domain\Exception;

/**
 * Un article portant ce slug existe déjà (quel que soit son statut).
 *
 * Levée par `CreateDraftArticleHandler` en pré-vérification (avant persist)
 * ou en rattrapage d'une collision concurrente (UniqueConstraintViolation
 * traduite en exception métier). Le contrôleur admin la capture pour poser
 * une erreur de champ propre sur le formulaire — jamais l'exception Doctrine
 * brute qui fuiterait le vocabulaire de persistance.
 */
final class ArticleSlugAlreadyExistsException extends \DomainException
{
    public static function forSlug(string $slug): self
    {
        return new self(\sprintf('Le slug "%s" est déjà utilisé par un autre article.', $slug));
    }
}
