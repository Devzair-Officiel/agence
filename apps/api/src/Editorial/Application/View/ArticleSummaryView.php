<?php

declare(strict_types=1);

namespace App\Editorial\Application\View;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ExpertiseIdentifier;

/**
 * Vue courte d'un article — utilisée par la liste `/api/resources`.
 *
 * On ne renvoie ni le corps markdown ni la SEO description : la page liste
 * n'en a pas besoin et les exposer allongerait inutilement le payload.
 */
final class ArticleSummaryView
{
    /**
     * @param list<string> $expertiseIds
     */
    public function __construct(
        public readonly string $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $excerpt,
        public readonly string $authorName,
        public readonly string $authorType,
        public readonly array $expertiseIds,
        public readonly string $publishedAt,
        public readonly string $updatedAt,
    ) {
    }

    public static function fromEntity(Article $article): self
    {
        $publishedAt = $article->publishedAt();
        if ($publishedAt === null) {
            // Ne devrait jamais arriver : le repository ne renvoie que des
            // articles publiés. Rester strict permet aux outils statiques de
            // ne pas voir un ?string.
            throw new \LogicException('Article publié sans publishedAt — invariant repository violé.');
        }

        return new self(
            id: $article->id()->toRfc4122(),
            slug: $article->slug()->value(),
            title: $article->title(),
            excerpt: $article->excerpt(),
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
