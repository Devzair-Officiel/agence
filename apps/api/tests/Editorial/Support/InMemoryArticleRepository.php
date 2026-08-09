<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Support;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\ExpertiseIdentifier;
use Symfony\Component\Uid\Uuid;

/**
 * Implémentation en mémoire pour les tests Application/Domain.
 *
 * Ne prétend pas répliquer PostgreSQL — juste garantir le contrat du port :
 * lecture uniquement des articles publiés dont `publishedAt <= $now`,
 * pagination stable, tri par publishedAt DESC puis id DESC.
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

    public function findBySlug(ArticleSlug $slug): ?Article
    {
        foreach ($this->articles as $article) {
            if ($article->slug()->equals($slug)) {
                return $article;
            }
        }

        return null;
    }

    public function findById(Uuid $id): ?Article
    {
        return $this->articles[$id->toRfc4122()] ?? null;
    }

    public function getPublishedBySlug(ArticleSlug $slug, \DateTimeImmutable $now): Article
    {
        foreach ($this->articles as $article) {
            if (!$article->slug()->equals($slug)) {
                continue;
            }
            if ($article->status() !== ArticleStatus::Published) {
                continue;
            }
            $publishedAt = $article->publishedAt();
            if ($publishedAt === null || $publishedAt > $now) {
                continue;
            }

            return $article;
        }

        throw ArticleNotFoundException::forSlug($slug->value());
    }

    public function listPublished(
        int $page,
        int $perPage,
        \DateTimeImmutable $now,
        ?ExpertiseIdentifier $expertise = null,
    ): array {
        $published = array_values(array_filter(
            $this->articles,
            fn (Article $article): bool => $this->isVisiblyPublished($article, $now, $expertise),
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

    public function countPublished(\DateTimeImmutable $now, ?ExpertiseIdentifier $expertise = null): int
    {
        return \count(array_filter(
            $this->articles,
            fn (Article $article): bool => $this->isVisiblyPublished($article, $now, $expertise),
        ));
    }

    private function isVisiblyPublished(
        Article $article,
        \DateTimeImmutable $now,
        ?ExpertiseIdentifier $expertise,
    ): bool {
        if ($article->status() !== ArticleStatus::Published) {
            return false;
        }
        $publishedAt = $article->publishedAt();
        if ($publishedAt === null || $publishedAt > $now) {
            return false;
        }
        if ($expertise === null) {
            return true;
        }

        foreach ($article->expertises() as $candidate) {
            if ($candidate === $expertise) {
                return true;
            }
        }

        return false;
    }
}
