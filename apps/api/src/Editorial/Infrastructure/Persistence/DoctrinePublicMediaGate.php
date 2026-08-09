<?php

declare(strict_types=1);

namespace App\Editorial\Infrastructure\Persistence;

use App\Editorial\Application\Media\PublicMediaGateInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

/**
 * Adaptateur Doctrine du port `PublicMediaGateInterface`.
 *
 * Utilise DBAL brut (pas de QueryBuilder ORM) pour deux raisons :
 *   - la question est purement scalaire (booléen) — hydratation Doctrine
 *     inutile ;
 *   - garde l'adaptateur totalement indépendant du mapping ORM d'`Article`
 *     et ne le fait pas dépendre transitivement d'`EditorialMedia`.
 *
 * Requête :
 *   SELECT 1 FROM editorial_article
 *   WHERE hero_media_id = :media
 *     AND status = 'published'
 *     AND published_at <= :now
 *   LIMIT 1
 *
 * Le doublage `status = 'published' AND published_at <= now` protège d'un
 * article publié avec date future (voir docblock d'`ArticleRepositoryInterface`).
 *
 * Perf : la colonne `hero_media_id` est indexée
 * (`idx_editorial_article_hero_media_id`, cf. migration 9B), donc la sonde
 * est O(log n) même sur un grand corpus d'articles.
 */
final class DoctrinePublicMediaGate implements PublicMediaGateInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function isReferencedByPublishedArticle(Uuid $mediaAssetId, \DateTimeImmutable $now): bool
    {
        $result = $this->connection->fetchOne(
            <<<'SQL'
            SELECT 1
            FROM editorial_article
            WHERE hero_media_id = :media
              AND status = 'published'
              AND published_at <= :now
            LIMIT 1
            SQL,
            [
                'media' => $mediaAssetId->toRfc4122(),
                'now' => $now->format('Y-m-d H:i:sO'),
            ],
        );

        return $result !== false;
    }
}
