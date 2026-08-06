<?php

declare(strict_types=1);

namespace App\Editorial\Infrastructure\Persistence;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Implémentation Doctrine du port `ArticleRepositoryInterface`.
 *
 * Ne fait aucun `flush` automatique : c'est la couche appelante (handler ou
 * commande CLI) qui décide du moment. Les lectures publiques filtrent
 * doublement sur `status = Published` ET `publishedAt <= :now` — voir la
 * doc du port pour la justification.
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

    public function findBySlug(ArticleSlug $slug): ?Article
    {
        $article = $this->entityManager
            ->getRepository(Article::class)
            ->findOneBy(['slug' => $slug->value()]);

        return $article instanceof Article ? $article : null;
    }

    public function findById(Uuid $id): ?Article
    {
        $article = $this->entityManager
            ->getRepository(Article::class)
            ->find($id);

        return $article instanceof Article ? $article : null;
    }

    public function getPublishedBySlug(ArticleSlug $slug, \DateTimeImmutable $now): Article
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.slug = :slug')
            ->andWhere('a.status = :status')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('slug', $slug->value())
            ->setParameter('status', ArticleStatus::Published)
            ->setParameter('now', $now)
            ->setMaxResults(1);

        $article = $qb->getQuery()->getOneOrNullResult();

        if (!$article instanceof Article) {
            throw ArticleNotFoundException::forSlug($slug->value());
        }

        return $article;
    }

    public function listPublished(int $page, int $perPage, \DateTimeImmutable $now): array
    {
        $offset = ($page - 1) * $perPage;

        $qb = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.status = :status')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('status', ArticleStatus::Published)
            ->setParameter('now', $now)
            ->orderBy('a.publishedAt', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        /** @var list<Article> $result */
        $result = array_values($qb->getQuery()->getResult());

        return $result;
    }

    public function countPublished(\DateTimeImmutable $now): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(Article::class, 'a')
            ->where('a.status = :status')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('status', ArticleStatus::Published)
            ->setParameter('now', $now);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
