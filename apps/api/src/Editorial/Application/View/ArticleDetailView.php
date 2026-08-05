<?php

declare(strict_types=1);

namespace App\Editorial\Application\View;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ExpertiseIdentifier;

/**
 * Vue complète d'un article — utilisée par `/api/resources/{slug}`.
 *
 * Depuis la Phase 8B2, la vue expose à la fois :
 *   - `body_markdown` : le corps Markdown source, conservé temporairement
 *     pour ne pas rompre le contrat public existant (débogage, dry-run
 *     éditorial, futurs consommateurs) ;
 *   - `content_html` : le rendu HTML sécurisé produit par le seul
 *     `CommonMarkArticleRenderer` de l'infra. C'est la représentation que
 *     le front public consomme (Symfony reste propriétaire de la frontière
 *     de confiance HTML — cf. ADR-011).
 *
 * Le DTO reste un objet valeur pur : il ne connaît ni le renderer ni la
 * politique de sécurité. Le handler construit les deux et les injecte.
 */
final class ArticleDetailView
{
    /**
     * @param list<string> $expertiseIds
     */
    public function __construct(
        public readonly string $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $excerpt,
        public readonly string $bodyMarkdown,
        public readonly string $contentHtml,
        public readonly string $seoTitle,
        public readonly string $seoDescription,
        public readonly string $authorName,
        public readonly string $authorType,
        public readonly array $expertiseIds,
        public readonly string $publishedAt,
        public readonly string $updatedAt,
    ) {
    }

    public static function fromEntity(Article $article, string $contentHtml): self
    {
        $publishedAt = $article->publishedAt();
        if ($publishedAt === null) {
            throw new \LogicException('Article publié sans publishedAt — invariant repository violé.');
        }

        $seo = $article->seo();

        return new self(
            id: $article->id()->toRfc4122(),
            slug: $article->slug()->value(),
            title: $article->title(),
            excerpt: $article->excerpt(),
            bodyMarkdown: $article->bodyMarkdown(),
            contentHtml: $contentHtml,
            seoTitle: $seo->title(),
            seoDescription: $seo->description(),
            authorName: $article->author()->name(),
            authorType: $article->author()->type()->value,
            expertiseIds: ExpertiseIdentifier::toList($article->expertises()),
            publishedAt: $publishedAt->format(\DateTimeInterface::ATOM),
            updatedAt: $article->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body_markdown' => $this->bodyMarkdown,
            'content_html' => $this->contentHtml,
            'seo' => [
                'title' => $this->seoTitle,
                'description' => $this->seoDescription,
            ],
            'author' => [
                'name' => $this->authorName,
                'type' => $this->authorType,
            ],
            'expertise_ids' => $this->expertiseIds,
            'published_at' => $this->publishedAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
