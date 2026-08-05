<?php

declare(strict_types=1);

namespace App\Editorial\Application\Markdown;

/**
 * Le fichier Markdown fourni est syntaxiquement invalide (front matter
 * manquant, YAML mal formé, encodage non-UTF-8, taille dépassée, corps vide).
 * Distincte de `MarkdownValidationException` — qui elle concerne le contenu
 * lui-même (HTML brut, URL dangereuse, champ inconnu, etc.).
 */
final class MarkdownParseException extends \RuntimeException
{
}
