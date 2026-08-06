<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleStatus;

/**
 * Ligne du tableau d'administration — vue plate, sans référence à Doctrine.
 *
 * Instances construites soit par le repository Doctrine (via `fromEntity`),
 * soit à la main par les tests d'application (constructeur direct).
 */
final class AdminArticleListItem
{
    public function __construct(
        public readonly string $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly ArticleStatus $status,
        public readonly ?\DateTimeImmutable $publishedAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(Article $article): self
    {
        return new self(
            id: $article->id()->toRfc4122(),
            slug: $article->slug()->value(),
            title: $article->title(),
            status: $article->status(),
            publishedAt: $article->publishedAt(),
            updatedAt: $article->updatedAt(),
        );
    }
}
