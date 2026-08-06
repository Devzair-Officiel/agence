<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Archive un article existant, quel que soit son statut source
 * (`Draft` ou `Published`). Idempotent : renvoie `alreadyArchived` sans
 * flush si l'article est déjà en `Archived`.
 */
final class ArchiveArticleHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(ArchiveArticle $command): ArchiveArticleResult
    {
        $article = $this->repository->findById($command->id);
        if ($article === null) {
            throw ArticleNotFoundException::forId($command->id->toRfc4122());
        }

        if ($article->status() === ArticleStatus::Archived) {
            return ArchiveArticleResult::alreadyArchived($article);
        }

        $article->archive($this->clock->now());
        $this->entityManager->flush();

        return ArchiveArticleResult::archived($article);
    }
}
