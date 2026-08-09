<?php

declare(strict_types=1);

namespace App\Editorial\Infrastructure\Persistence;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\ExpertiseIdentifier;
use Doctrine\DBAL\ParameterType;
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

    public function listPublished(
        int $page,
        int $perPage,
        \DateTimeImmutable $now,
        ?ExpertiseIdentifier $expertise = null,
    ): array {
        if ($expertise !== null) {
            return $this->listPublishedFilteredByExpertise($page, $perPage, $now, $expertise);
        }

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

    public function countPublished(\DateTimeImmutable $now, ?ExpertiseIdentifier $expertise = null): int
    {
        if ($expertise !== null) {
            $count = $this->entityManager->getConnection()->fetchOne(
                <<<'SQL'
                SELECT COUNT(*)
                FROM editorial_article
                WHERE status = 'published'
                  AND published_at <= :now
                  AND expertise_ids @> CAST(:expertise AS jsonb)
                SQL,
                [
                    'now' => $now->format('Y-m-d H:i:sO'),
                    'expertise' => \json_encode([$expertise->value], \JSON_THROW_ON_ERROR),
                ],
            );

            return (int) $count;
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(Article::class, 'a')
            ->where('a.status = :status')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('status', ArticleStatus::Published)
            ->setParameter('now', $now);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Filtrage JSONB `expertise_ids @> '["<value>"]'` via DBAL brut : Doctrine
     * ORM ne dispose pas d'opérateur natif pour l'inclusion dans un tableau
     * JSON PostgreSQL et l'ajout d'une DQL function serait disproportionné
     * pour un unique filtre. On sélectionne d'abord les identifiants, puis on
     * hydrate les agrégats via l'ORM en préservant l'ordre chronologique.
     *
     * @return list<Article>
     */
    private function listPublishedFilteredByExpertise(
        int $page,
        int $perPage,
        \DateTimeImmutable $now,
        ExpertiseIdentifier $expertise,
    ): array {
        $offset = ($page - 1) * $perPage;
        $connection = $this->entityManager->getConnection();

        /** @var list<string> $ids */
        $ids = $connection->fetchFirstColumn(
            <<<'SQL'
            SELECT id
            FROM editorial_article
            WHERE status = 'published'
              AND published_at <= :now
              AND expertise_ids @> CAST(:expertise AS jsonb)
            ORDER BY published_at DESC, id DESC
            LIMIT :limit OFFSET :offset
            SQL,
            [
                'now' => $now->format('Y-m-d H:i:sO'),
                'expertise' => \json_encode([$expertise->value], \JSON_THROW_ON_ERROR),
                'limit' => $perPage,
                'offset' => $offset,
            ],
            [
                'limit' => ParameterType::INTEGER,
                'offset' => ParameterType::INTEGER,
            ],
        );

        if ($ids === []) {
            return [];
        }

        /** @var list<Article> $articles */
        $articles = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->where('a.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($articles as $article) {
            $indexed[$article->id()->toRfc4122()] = $article;
        }

        $ordered = [];
        foreach ($ids as $id) {
            $key = (string) $id;
            if (isset($indexed[$key])) {
                $ordered[] = $indexed[$key];
            }
        }

        return $ordered;
    }
}
