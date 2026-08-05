<?php

declare(strict_types=1);

namespace App\Editorial\Infrastructure\Markdown;

use League\CommonMark\MarkdownConverter;

/**
 * Rendu HTML CommonMark sécurisé, réutilisable.
 *
 * En Phase 8B1 le rendu n'est pas exposé publiquement (le contrat HTTP
 * expose `body_markdown`, pas `body_html`). Cette classe existe pour :
 *
 * - fournir une preview déterministe au CLI `app:editorial:import --dry-run` ;
 * - garantir qu'un article importé passe bien au rendu sans erreur avant
 *   d'atteindre la base ;
 * - poser dès à présent le point d'accroche stable pour la Phase 8B2 (rendu
 *   côté Nuxt via l'API), en évitant qu'on multiplie les convertisseurs
 *   configurés différemment.
 */
final class CommonMarkArticleRenderer
{
    private readonly MarkdownConverter $converter;

    public function __construct(MarkdownSecurityPolicy $policy)
    {
        $this->converter = new MarkdownConverter($policy->environment());
    }

    public function renderHtml(string $bodyMarkdown): string
    {
        return $this->converter->convert($bodyMarkdown)->getContent();
    }
}
