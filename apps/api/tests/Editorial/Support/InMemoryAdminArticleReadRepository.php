<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Support;

use App\Editorial\Application\Query\AdminArticleEditView;
use App\Editorial\Application\Query\AdminArticleListItem;
use App\Editorial\Application\Query\AdminArticleReadRepositoryInterface;
use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleStatus;
use Symfony\Component\Uid\Uuid;

/**
 * Implémentation en mémoire du port admin, alignée sur le contrat Doctrine
 * (tri `updatedAt DESC, id DESC`, aucun filtre `publishedAt <= now`).
 */
final class InMemoryAdminArticleReadRepository implements AdminArticleReadRepositoryInterface
{
    /**
     * @var array<string, Article>
     */
    private array $articles = [];

    public function save(Article $article): void
    {
        $this->articles[$article->id()->toRfc4122()] = $article;
    }

    public function paginate(int $page, int $perPage, ?ArticleStatus $statusFilter): array
    {
        $filtered = $this->filtered($statusFilter);
        usort($filtered, static function (Article $a, Article $b): int {
            $cmp = $b->updatedAt() <=> $a->updatedAt();
            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp($b->id()->toRfc4122(), $a->id()->toRfc4122());
        });

        $slice = array_values(\array_slice($filtered, ($page - 1) * $perPage, $perPage));

        return array_map(
            static fn (Article $article): AdminArticleListItem => AdminArticleListItem::fromEntity($article),
            $slice,
        );
    }

    public function count(?ArticleStatus $statusFilter): int
    {
        return \count($this->filtered($statusFilter));
    }

    public function findForEdit(Uuid $id): ?AdminArticleEditView
    {
        $article = $this->articles[$id->toRfc4122()] ?? null;

        return $article === null ? null : AdminArticleEditView::fromEntity($article);
    }

    /**
     * @return list<Article>
     */
    private function filtered(?ArticleStatus $statusFilter): array
    {
        if ($statusFilter === null) {
            return array_values($this->articles);
        }

        return array_values(array_filter(
            $this->articles,
            static fn (Article $article): bool => $article->status() === $statusFilter,
        ));
    }
}
