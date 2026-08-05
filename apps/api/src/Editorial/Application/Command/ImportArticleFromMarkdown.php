<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

/**
 * Import d'un article Markdown depuis un chemin de fichier local.
 *
 * `dryRun = true` : le handler parse, valide et retourne le résultat sans
 * écrire en base ni flusher. Utilisé par `app:editorial:import --dry-run`.
 */
final class ImportArticleFromMarkdown
{
    public function __construct(
        public readonly string $filePath,
        public readonly bool $dryRun = false,
    ) {
    }
}
