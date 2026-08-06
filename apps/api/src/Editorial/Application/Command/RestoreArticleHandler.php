<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Ramène un article archivé vers `Draft` afin de le rouvrir à l'édition.
 * Idempotent quand l'article est déjà en `Draft`. Refuse (via l'agrégat)
 * une restauration depuis `Published` : il faut d'abord archiver.
 */
final class RestoreArticleHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(RestoreArticle $command): RestoreArticleResult
    {
        $article = $this->repository->findById($command->id);
        if ($article === null) {
            throw ArticleNotFoundException::forId($command->id->toRfc4122());
        }

        if ($article->status() === ArticleStatus::Draft) {
            return RestoreArticleResult::alreadyDraft($article);
        }

        $article->restore($this->clock->now());
        $this->entityManager->flush();

        return RestoreArticleResult::restored($article);
    }
}
