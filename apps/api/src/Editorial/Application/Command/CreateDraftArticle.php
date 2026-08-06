<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\ExpertiseIdentifier;

/**
 * Création d'un article en statut `Draft`.
 *
 * Tous les champs sont non nuls : c'est un aggregate root complet, pas un
 * patch. Le slug est obligatoire à cette étape (unique responsabilité de
 * cette commande — l'édition ultérieure ne pourra pas le modifier).
 */
final class CreateDraftArticle
{
    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $excerpt,
        public readonly string $bodyMarkdown,
        public readonly string $seoTitle,
        public readonly string $seoDescription,
        public readonly string $authorName,
        public readonly AuthorType $authorType,
        public readonly array $expertises,
    ) {
    }
}
