<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Retire l'image principale d'un brouillon.
 *
 * Contrats :
 *   - Draft + image → retire image + alt, met à jour `updatedAt`, flush.
 *   - Draft sans image → no-op silencieux, aucun flush.
 *   - Published/Archived → refusé par l'agrégat (`ArticleNotEditableException`).
 *
 * La suppression physique du `MediaAsset` n'est PAS un concern de ce
 * handler — la Phase 9B n'introduit aucun garbage collector média
 * (voir brief §10). Un média retiré d'un article reste réutilisable par
 * d'autres articles depuis la bibliothèque.
 */
final class RemoveDraftArticleHeroImageHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(RemoveDraftArticleHeroImage $command): RemoveDraftArticleHeroImageResult
    {
        $article = $this->repository->findById($command->articleId);
        if ($article === null) {
            throw ArticleNotFoundException::forId($command->articleId->toRfc4122());
        }

        $before = $article->updatedAt();
        $article->changeHeroImage(null, $this->clock->now());

        if ($article->updatedAt() !== $before) {
            $this->entityManager->flush();

            return RemoveDraftArticleHeroImageResult::mutated($article);
        }

        return RemoveDraftArticleHeroImageResult::unchanged($article);
    }
}
