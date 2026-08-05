<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Passe un article de Draft à Published.
 *
 * Règles :
 * - le slug doit exister (sinon `ArticleNotFoundException`) ;
 * - si `publishedAt` est explicite, il doit être ≤ maintenant (l'agrégat
 *   refuse aussi, en profondeur) ;
 * - l'appel est idempotent : republier un article déjà publié ne fait
 *   rien (l'agrégat garde le statut et la date d'origine).
 */
final class PublishArticleBySlugHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(PublishArticleBySlug $command): PublishArticleResult
    {
        $article = $this->repository->findBySlug($command->slug);
        if ($article === null) {
            throw ArticleNotFoundException::forSlug($command->slug->value());
        }

        if ($article->status() === ArticleStatus::Published) {
            return PublishArticleResult::alreadyPublished($article);
        }

        $now = $this->clock->now();
        $publishedAt = $command->publishedAt ?? $now;

        if ($publishedAt > $now) {
            throw new ArticleInvariantViolation(
                'La date de publication demandée est dans le futur ; refusée.',
            );
        }

        $article->publish($publishedAt, $now);
        $this->entityManager->flush();

        return PublishArticleResult::published($article);
    }
}
