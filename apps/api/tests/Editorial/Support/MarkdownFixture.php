<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Support;

/**
 * Fabrique des fichiers Markdown valides ou personnalisés dans un répertoire
 * temporaire pour les tests d'import (handler et CLI).
 *
 * Chaque fixture crée son propre répertoire pour éviter les collisions inter-tests
 * et se supprime via `cleanup()` (`register_shutdown_function` de secours au cas où).
 */
final class MarkdownFixture
{
    private string $directory;

    /** @var list<string> */
    private array $files = [];

    public function __construct()
    {
        $dir = tempnam(sys_get_temp_dir(), 'md-fixture-');
        \assert(\is_string($dir));
        unlink($dir);
        mkdir($dir);
        $this->directory = $dir;
    }

    public function directory(): string
    {
        return $this->directory;
    }

    public function writeValid(string $filename = 'article.md', ?string $slug = null): string
    {
        return $this->write($filename, self::validArticle($slug ?? 'exemple-import'));
    }

    public function write(string $filename, string $content): string
    {
        $path = $this->directory.\DIRECTORY_SEPARATOR.$filename;
        file_put_contents($path, $content);
        $this->files[] = $path;

        return $path;
    }

    public function cleanup(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        if (is_dir($this->directory)) {
            @rmdir($this->directory);
        }
    }

    public static function validArticle(string $slug = 'exemple-import'): string
    {
        return <<<MD
        ---
        slug: {$slug}
        title: Guide interne sur la structuration éditoriale
        excerpt: Un aperçu synthétique du guide, assez long pour dépasser la borne minimale imposée par le domaine.
        seo:
          title: Structurer un projet éditorial durable pour son agence
          description: "Guide court sur la structuration éditoriale : gouvernance, workflow, mesure. Adapté à une agence de taille humaine."
        author:
          name: Devzair
          type: organization
        expertises:
          - concevoir
          - visibilite
        ---
        ## Introduction

        Un paragraphe assez long pour être crédible et pour tester le pipeline.

        - un item
        - un autre
        MD;
    }

    public static function articleWithHtml(string $slug = 'article-html'): string
    {
        return <<<MD
        ---
        slug: {$slug}
        title: Guide interne sur la structuration éditoriale
        excerpt: Un aperçu synthétique du guide, assez long pour dépasser la borne minimale imposée par le domaine.
        seo:
          title: Structurer un projet éditorial durable pour son agence
          description: "Guide court sur la structuration éditoriale : gouvernance, workflow, mesure. Adapté à une agence de taille humaine."
        author:
          name: Devzair
          type: organization
        expertises:
          - concevoir
        ---
        ## Introduction

        Un paragraphe.

        <div>HTML brut interdit</div>
        MD;
    }
}
