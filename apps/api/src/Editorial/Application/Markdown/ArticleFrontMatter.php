<?php

declare(strict_types=1);

namespace App\Editorial\Application\Markdown;

use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\Author;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Domain\SeoMetadata;

/**
 * Représentation typée du front matter YAML d'un article.
 *
 * Ce VO est construit par `MarkdownArticleFileParser` — jamais directement
 * par le code appelant — car il applique la validation stricte (champs
 * requis, refus des champs inconnus, refus de `publishedAt`).
 *
 * Choix : `publishedAt` n'est PAS présent dans le front matter. La date de
 * publication est fournie exclusivement par la commande CLI
 * `app:editorial:publish --published-at=…`, pour éviter que le pipeline
 * d'import puisse rendre un article visible sans passer par l'étape de
 * publication explicite.
 */
final class ArticleFrontMatter
{
    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    private function __construct(
        public readonly ArticleSlug $slug,
        public readonly string $title,
        public readonly string $excerpt,
        public readonly SeoMetadata $seo,
        public readonly Author $author,
        public readonly array $expertises,
    ) {
    }

    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    public static function create(
        ArticleSlug $slug,
        string $title,
        string $excerpt,
        SeoMetadata $seo,
        Author $author,
        array $expertises,
    ): self {
        return new self($slug, $title, $excerpt, $seo, $author, $expertises);
    }

    public static function authorTypeFromString(string $raw): AuthorType
    {
        $normalized = strtolower(trim($raw));

        return match ($normalized) {
            'organization' => AuthorType::Organization,
            'person' => AuthorType::Person,
            default => throw new MarkdownValidationException(
                \sprintf('author.type doit valoir "organization" ou "person" (reçu : "%s").', $raw),
            ),
        };
    }
}
