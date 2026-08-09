<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Application\Media\HeroMediaNotFoundException;
use App\Editorial\Application\Media\MediaAssetLookupInterface;
use App\Editorial\Domain\ArticleHeroImage;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Rattache (ou remplace) l'image principale d'un brouillon.
 *
 * Séquence STRICTEMENT ordonnée — pré-validation puis mutation :
 *   1. Charger l'article par UUID (404 si inexistant).
 *   2. Construire le VO `ArticleHeroImage` — la validation d'invariants
 *      (alt non vide, longueur bornée, trim) est portée par le VO. Toute
 *      violation lève avant tout accès au média.
 *   3. Vérifier l'existence du `MediaAsset` via le port
 *      `MediaAssetLookupInterface`. Si absent : `HeroMediaNotFoundException`
 *      — l'agrégat n'est TOUCHÉ à AUCUN moment.
 *   4. Appliquer la mutation via `Article::changeHeroImage()` (le domaine
 *      gère seul le refus si l'article n'est pas Draft).
 *   5. Flush UNIQUEMENT si l'agrégat a réellement muté (comparaison de
 *      `updatedAt` avant/après) — le no-op se propage naturellement en
 *      absence de write DB.
 *
 * Aucun couplage HTTP dans ce handler : pas d'accès à la Request, pas de
 * flash, pas de redirection. Ces responsabilités appartiennent à
 * `AdminArticleHeroImageController`.
 */
final class SetDraftArticleHeroImageHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
        private readonly MediaAssetLookupInterface $mediaLookup,
    ) {
    }

    public function __invoke(SetDraftArticleHeroImage $command): SetDraftArticleHeroImageResult
    {
        $article = $this->repository->findById($command->articleId);
        if ($article === null) {
            throw ArticleNotFoundException::forId($command->articleId->toRfc4122());
        }

        // 1) Pré-validation : construction du VO (alt non vide, borné).
        //    Toute violation d'invariant lève ici, sans toucher l'agrégat.
        $heroImage = ArticleHeroImage::create($command->mediaAssetId, $command->altText);

        // 2) Pré-validation cross-context : existence du média.
        //    Le port `MediaAssetLookupInterface` ne renvoie qu'un booléen —
        //    aucun risque de charger transitivement une entité `MediaAsset`.
        if (!$this->mediaLookup->exists($command->mediaAssetId)) {
            throw HeroMediaNotFoundException::forId($command->mediaAssetId);
        }

        // 3) Mutation via l'agrégat — le domaine porte le refus Published/Archived
        //    (`assertDraftEditable`) et la garantie d'idempotence (no-op sur
        //    valeurs équivalentes).
        $before = $article->updatedAt();
        $article->changeHeroImage($heroImage, $this->clock->now());

        if ($article->updatedAt() !== $before) {
            $this->entityManager->flush();

            return SetDraftArticleHeroImageResult::mutated($article);
        }

        return SetDraftArticleHeroImageResult::unchanged($article);
    }
}
