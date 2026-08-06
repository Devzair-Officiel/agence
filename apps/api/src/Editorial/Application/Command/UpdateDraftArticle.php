<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\ExpertiseIdentifier;
use Symfony\Component\Uid\Uuid;

/**
 * Mutation atomique d'un article en statut `Draft`.
 *
 * Toute propriété laissée à `null` est ignorée (patch partiel). Les
 * propriétés fournies sont validées puis appliquées collectivement — si
 * un champ échoue à la validation, l'article entier reste inchangé.
 *
 * `expertises` : `null` = pas de modification ; liste non nulle = liste
 * complète de remplacement. On ne fait pas d'add/remove ciblés (KISS).
 *
 * `author` : décrit par un couple (nom, type) pour éviter de construire
 * le VO côté appelant — la validation reste dans le domaine.
 *
 * `slug` immuable en Phase 8C : jamais modifiable via ce chemin.
 */
final class UpdateDraftArticle
{
    /**
     * @param list<ExpertiseIdentifier>|null $expertises
     */
    public function __construct(
        public readonly Uuid $id,
        public readonly ?string $title = null,
        public readonly ?string $excerpt = null,
        public readonly ?string $bodyMarkdown = null,
        public readonly ?string $seoTitle = null,
        public readonly ?string $seoDescription = null,
        public readonly ?string $authorName = null,
        public readonly ?\App\Editorial\Domain\AuthorType $authorType = null,
        public readonly ?array $expertises = null,
    ) {
    }
}
