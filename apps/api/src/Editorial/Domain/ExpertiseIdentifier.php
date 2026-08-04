<?php

declare(strict_types=1);

namespace App\Editorial\Domain;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;

/**
 * Piliers d'expertise déjà publiés côté Nuxt (`apps/web/app/config/pillars.ts`).
 *
 * Il n'y a pas de dépendance directe entre backend et front : c'est la
 * responsabilité éditoriale de garder ces cinq clefs alignées. Les tests
 * garantissent que toute tentative d'ajouter un identifiant hors de cette
 * liste est refusée à la construction de l'article.
 */
enum ExpertiseIdentifier: string
{
    case Concevoir = 'concevoir';
    case Construire = 'construire';
    case Valoriser = 'valoriser';
    case Visibilite = 'visibilite';
    case FaireEvoluer = 'faire-evoluer';

    /**
     * @param list<string> $ids
     *
     * @return list<self>
     */
    public static function fromList(array $ids): array
    {
        $normalized = [];
        foreach ($ids as $id) {
            $case = self::tryFrom($id);
            if ($case === null) {
                throw new ArticleInvariantViolation(\sprintf(
                    'Identifiant de pilier inconnu : "%s".',
                    $id,
                ));
            }
            $normalized[$case->value] = $case;
        }

        return array_values($normalized);
    }

    /**
     * @param list<self> $identifiers
     *
     * @return list<string>
     */
    public static function toList(array $identifiers): array
    {
        return array_map(static fn (self $id): string => $id->value, $identifiers);
    }
}
