<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http\Form;

use App\Editorial\Domain\ExpertiseIdentifier;

/**
 * DTO commun aux formulaires create/edit. On garde les champs texte
 * strictement — la conversion en VO se fait dans le handler, qui reste
 * l'unique gardien des invariants métier.
 *
 * L'ordre des champs correspond à celui rendu dans `_form.html.twig` afin
 * que l'auteur de template puisse se référer à la liste ici.
 *
 * `slug` est présent ici pour la création ; il est ignoré à l'édition (voir
 * `ArticleEditData::hydrate`).
 */
final class ArticleFormPayload
{
    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    public function __construct(
        public string $slug = '',
        public string $title = '',
        public string $excerpt = '',
        public string $bodyMarkdown = '',
        public string $seoTitle = '',
        public string $seoDescription = '',
        public string $authorName = '',
        public string $authorType = 'organization',
        public array $expertises = [],
    ) {
    }
}
