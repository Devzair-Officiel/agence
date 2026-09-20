<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\Exception\ArticleNotDeletableException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Supprime définitivement un article archivé.
 *
 * Invariant métier garanti ici (côté domaine/application), indépendamment
 * de toute vérification UI :
 *   - l'article doit exister → sinon `ArticleNotFoundException` ;
 *   - l'article doit être en statut `Archived` → sinon
 *     `ArticleNotDeletableException`.
 *
 * Les médias référencés (`heroMediaId`) ne sont PAS supprimés : l'article
 * est le seul propriétaire de l'enregistrement `editorial_article`, mais
 * pas nécessairement du fichier média (pas de FK, isolation inter-contexte).
 * Seule l'association disparaît avec la ligne.
 */
final class DeleteArchivedArticleHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ArticleNotFoundException      si l'UUID est inconnu
     * @throws ArticleNotDeletableException  si l'article n'est pas archivé
     */
    public function __invoke(DeleteArchivedArticle $command): void
    {
        $article = $this->repository->findById($command->id);
        if ($article === null) {
            throw ArticleNotFoundException::forId($command->id->toRfc4122());
        }

        if ($article->status() !== ArticleStatus::Archived) {
            throw ArticleNotDeletableException::becauseNotArchived($article->status());
        }

        $this->repository->remove($article);
        $this->entityManager->flush();
    }
}
