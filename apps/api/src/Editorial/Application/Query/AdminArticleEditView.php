<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\ExpertiseIdentifier;

/**
 * Vue plate d'un article pour l'écran d'édition administrateur.
 *
 * Distinct de `ArticleDetailView` (public) :
 *   - inclut le statut et la date brute (utile aux templates admin) ;
 *   - n'embarque pas le rendu HTML — l'admin travaille sur la source Markdown ;
 *   - ne franchit pas la frontière `/api/**` : c'est un DTO de présentation
 *     interne.
 */
final class AdminArticleEditView
{
    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    public function __construct(
        public readonly string $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $excerpt,
        public readonly string $bodyMarkdown,
        public readonly string $seoTitle,
        public readonly string $seoDescription,
        public readonly string $authorName,
        public readonly AuthorType $authorType,
        public readonly array $expertises,
        public readonly ArticleStatus $status,
        public readonly ?\DateTimeImmutable $publishedAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(Article $article): self
    {
        $seo = $article->seo();
        $author = $article->author();

        return new self(
            id: $article->id()->toRfc4122(),
            slug: $article->slug()->value(),
            title: $article->title(),
            excerpt: $article->excerpt(),
            bodyMarkdown: $article->bodyMarkdown(),
            seoTitle: $seo->title(),
            seoDescription: $seo->description(),
            authorName: $author->name(),
            authorType: $author->type(),
            expertises: $article->expertises(),
            status: $article->status(),
            publishedAt: $article->publishedAt(),
            updatedAt: $article->updatedAt(),
        );
    }
}
