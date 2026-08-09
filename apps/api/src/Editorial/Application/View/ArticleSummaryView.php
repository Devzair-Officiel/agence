<?php

declare(strict_types=1);

namespace App\Editorial\Application\View;

use App\Editorial\Application\Media\MediaAssetDescriptor;
use App\Editorial\Domain\Article;
use App\Editorial\Domain\ExpertiseIdentifier;

/**
 * Vue courte d'un article — utilisée par la liste `/api/resources`.
 *
 * On ne renvoie ni le corps markdown ni la SEO description : la page liste
 * n'en a pas besoin et les exposer allongerait inutilement le payload.
 *
 * Depuis la Phase 9B : `heroImage` (nullable) — carte de listing avec image.
 * L'assemblage résout le descripteur média via l'ACL applicative, jamais via
 * jointure Doctrine transverse (`Editorial` reste isolé de `EditorialMedia`).
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
        public readonly ?PublicArticleImageView $heroImage = null,
    ) {
    }

    /**
     * Reconstitue la vue à partir de l'agrégat, en injectant le descripteur
     * média fourni par l'appelant (résolu via `MediaAssetReaderInterface`).
     *
     * Si l'article porte une référence média mais que le descripteur n'a pu
     * être résolu (race rare entre la lecture Article et la lecture Media),
     * on renvoie la vue SANS image plutôt que de tomber en 500 — c'est
     * cohérent avec le read-model admin.
     */
    public static function fromEntity(Article $article, ?MediaAssetDescriptor $heroDescriptor = null): self
    {
        $publishedAt = $article->publishedAt();
        if ($publishedAt === null) {
            // Ne devrait jamais arriver : le repository ne renvoie que des
            // articles publiés. Rester strict permet aux outils statiques de
            // ne pas voir un ?string.
            throw new \LogicException('Article publié sans publishedAt — invariant repository violé.');
        }

        $heroVo = $article->heroImage();
        $heroView = null;
        if ($heroVo !== null && $heroDescriptor !== null && $heroVo->mediaAssetId()->equals($heroDescriptor->id)) {
            $heroView = PublicArticleImageView::fromDescriptor($heroVo, $heroDescriptor);
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
            heroImage: $heroView,
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
            'hero_image' => $this->heroImage?->toArray(),
        ];
    }
}
