<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\Exception\InvalidArticleTransitionException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Publie un brouillon adressé par UUID depuis l'administration.
 *
 * Différences avec `PublishArticleBySlugHandler` (chemin CLI) :
 *   - charge par identifiant stable, pas par slug ;
 *   - `publishedAt = now` implicite (pas de fenêtre planifiée) ;
 *   - refuse strictement toute publication depuis `Published` ou `Archived`
 *     (directive Phase 8C3 n°2/n°3) — l'archivage doit d'abord être restauré,
 *     et un article déjà publié ne se re-publie pas.
 *
 * Refuser explicitement le cas `Archived → Published` ici (et pas seulement
 * dans le contrôleur) garantit que la règle métier reste couverte par un
 * test unitaire d'application et qu'elle protège tous les appelants futurs
 * (message bus, CLI dédiée, etc.), pas uniquement HTTP.
 */
final class PublishDraftArticleHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(PublishDraftArticle $command): PublishDraftArticleResult
    {
        $article = $this->repository->findById($command->id);
        if ($article === null) {
            throw ArticleNotFoundException::forId($command->id->toRfc4122());
        }

        if ($article->status() !== ArticleStatus::Draft) {
            throw InvalidArticleTransitionException::cannotPublishFrom($article->status());
        }

        $now = $this->clock->now();
        $article->publish($now, $now);
        $this->entityManager->flush();

        return new PublishDraftArticleResult($article);
    }
}
