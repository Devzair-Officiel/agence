<?php

declare(strict_types=1);

namespace App\Editorial\Domain\Exception;

use App\Editorial\Domain\ArticleStatus;

/**
 * Levée quand une suppression définitive est demandée sur un article
 * qui n'est pas en statut `Archived`.
 *
 * Seul un article archivé peut être supprimé définitivement — règle métier
 * garantie par le handler indépendamment de l'UI. La couche de présentation
 * retourne un 303 See Other + flash error lorsque cette exception est levée
 * (parcours back-office HTML — pas d'API JSON sur cet endpoint).
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
