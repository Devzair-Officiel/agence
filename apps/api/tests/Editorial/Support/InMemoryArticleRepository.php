<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Support;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;

/**
 * Implémentation en mémoire pour les tests Application/Domain.
 *
 * Ne prétend pas répliquer PostgreSQL — juste garantir le contrat du port :
 * lecture uniquement des articles publiés, pagination stable, tri par
 * publishedAt DESC puis id DESC.
 */
final class InMemoryArticleRepository implements ArticleRepositoryInterface
{
    /**
     * @var array<string, Article>
     */
    private array $articles = [];

    public function save(Article $article): void
    {
        $this->articles[$article->id()->toRfc4122()] = $article;
    }

    public function getPublishedBySlug(ArticleSlug $slug): Article
    {
        foreach ($this->articles as $article) {
            if (!$article->slug()->equals($slug)) {
                continue;
            }
            if ($article->status() !== ArticleStatus::Published) {
                continue;
            }

            return $article;
        }

        throw ArticleNotFoundException::forSlug($slug->value());
    }

    public function listPublished(int $page, int $perPage): array
    {
        $published = array_values(array_filter(
            $this->articles,
            static fn (Article $article): bool => $article->status() === ArticleStatus::Published,
        ));

        usort($published, static function (Article $a, Article $b): int {
            $aDate = $a->publishedAt();
            $bDate = $b->publishedAt();
            \assert($aDate !== null && $bDate !== null);
            $cmp = $bDate <=> $aDate;
            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp(
                $b->id()->toRfc4122(),
                $a->id()->toRfc4122(),
            );
        });

        $offset = ($page - 1) * $perPage;

        return array_values(\array_slice($published, $offset, $perPage));
    }

    public function countPublished(): int
    {
        return \count(array_filter(
            $this->articles,
            static fn (Article $article): bool => $article->status() === ArticleStatus::Published,
        ));
    }
}
