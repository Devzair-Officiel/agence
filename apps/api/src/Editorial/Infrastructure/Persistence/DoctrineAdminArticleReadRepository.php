<?php

declare(strict_types=1);

namespace App\Editorial\Infrastructure\Persistence;

use App\Editorial\Application\Query\AdminArticleEditView;
use App\Editorial\Application\Query\AdminArticleListItem;
use App\Editorial\Application\Query\AdminArticleReadRepositoryInterface;
use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Adaptateur Doctrine du port admin. Retourne des DTO plats, jamais
 * l'entité Doctrine — la couche présentation ne doit pas piloter l'agrégat.
 *
 * Tri stable : `updated_at DESC, id DESC`. On préfère `updated_at` à
 * `published_at` car l'admin voit tous les statuts (draft/archived n'ont pas
 * toujours de `publishedAt`).
 */
final class DoctrineAdminArticleReadRepository implements AdminArticleReadRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function paginate(int $page, int $perPage, ?ArticleStatus $statusFilter): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->orderBy('a.updatedAt', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        if ($statusFilter !== null) {
            $qb->andWhere('a.status = :status')->setParameter('status', $statusFilter);
        }

        return array_values(array_map(
            static fn (Article $article): AdminArticleListItem => AdminArticleListItem::fromEntity($article),
            $qb->getQuery()->getResult(),
        ));
    }

    public function count(?ArticleStatus $statusFilter): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(Article::class, 'a');

        if ($statusFilter !== null) {
            $qb->andWhere('a.status = :status')->setParameter('status', $statusFilter);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findForEdit(Uuid $id): ?AdminArticleEditView
    {
        $article = $this->entityManager
            ->getRepository(Article::class)
            ->find($id);

        return $article instanceof Article ? AdminArticleEditView::fromEntity($article) : null;
    }
}
