<?php

declare(strict_types=1);

namespace App\Editorial\Domain\Exception;

/**
 * Levée par le repository quand un article demandé n'existe pas — ou existe
 * mais n'est pas publié (le domaine refuse d'exposer un brouillon).
 * Traduite en 404 par le controller.
 */
final class ArticleNotFoundException extends \DomainException
{
    public static function forSlug(string $slug): self
    {
        return new self(\sprintf('Aucun article publié pour le slug "%s".', $slug));
    }
}
