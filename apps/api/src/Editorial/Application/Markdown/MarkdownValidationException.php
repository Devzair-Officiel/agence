<?php

declare(strict_types=1);

namespace App\Editorial\Application\Markdown;

/**
 * Le contenu Markdown viole la politique de sécurité éditoriale :
 * HTML brut détecté, lien vers un schéma non autorisé (`javascript:`,
 * `data:`, `vbscript:`), champ front matter inconnu, ou toute règle
 * refusée avant la construction de l'article.
 *
 * Contrairement à `MarkdownParseException`, l'entrée est syntaxiquement
 * valide — elle est refusée pour raisons de politique, pas de parsing.
 */
final class MarkdownValidationException extends \RuntimeException
{
    /**
     * @param list<string> $violations
     */
    public function __construct(string $message, public readonly array $violations = [])
    {
        parent::__construct($message);
    }
}
