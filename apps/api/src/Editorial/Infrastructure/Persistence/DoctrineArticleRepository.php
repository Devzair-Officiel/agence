<?php

declare(strict_types=1);

namespace App\Editorial\Infrastructure\Persistence;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Implémentation Doctrine du port `ArticleRepositoryInterface`.
 *
 * Ne fait aucun `flush` automatique : c'est la couche appelante (le handler
 * ou une future commande d'écriture Phase 8B) qui décide du moment.
 * Les lectures publiques filtrent systématiquement sur `status = Published`.
 */
final class DoctrineArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Article $article): void
    {
        $this->entityManager->persist($article);
    }

    public function getPublishedBySlug(ArticleSlug $slug): Article
    {
        $article = $this->entityManager
            ->getRepository(Article::class)
            ->findOneBy([
                'slug' => $slug->value(),
                'status' => ArticleStatus::Published,
            ]);

        if (!$article instanceof Article) {
            throw ArticleNotFoundException::forSlug($slug->value());
        }

        return $article;
    }

    public function listPublished(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        $qb = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.status = :status')
            ->setParameter('status', ArticleStatus::Published)
            ->orderBy('a.publishedAt', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        /** @var list<Article> $result */
        $result = array_values($qb->getQuery()->getResult());

        return $result;
    }

    public function countPublished(): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(Article::class, 'a')
            ->where('a.status = :status')
            ->setParameter('status', ArticleStatus::Published);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
