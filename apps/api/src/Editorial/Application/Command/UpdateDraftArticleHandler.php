<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\Author;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\SeoMetadata;
use App\Editorial\Infrastructure\Markdown\MarkdownContentValidator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Applique une mutation atomique à un article en `Draft`.
 *
 * Étapes strictement ordonnées :
 * 1. Charger l'article par UUID (404 si inexistant).
 * 2. Construire les VOs (SeoMetadata, Author), valider le Markdown, et
 *    vérifier les invariants de `createdAt` — tout ce qui peut échouer est
 *    traité ici, avant toute écriture sur l'agrégat.
 * 3. Appliquer les mutations une à une (chacune est no-op si valeur
 *    identique — l'agrégat gère, et vérifie à nouveau le statut Draft).
 * 4. Un seul `flush` en fin de traitement si au moins une mutation a eu lieu.
 *
 * Atomicité : si la pré-validation échoue (exception levée à l'étape 2),
 * aucune propriété de l'agrégat n'a été modifiée.
 */
final class UpdateDraftArticleHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
        private readonly MarkdownContentValidator $markdownValidator,
    ) {
    }

    public function __invoke(UpdateDraftArticle $command): UpdateDraftArticleResult
    {
        $article = $this->repository->findById($command->id);
        if ($article === null) {
            throw ArticleNotFoundException::forId($command->id->toRfc4122());
        }

        // 1) Pré-validation exhaustive avant toute mutation.
        $now = $this->clock->now();

        $seo = null;
        if ($command->seoTitle !== null || $command->seoDescription !== null) {
            if ($command->seoTitle === null || $command->seoDescription === null) {
                throw new ArticleInvariantViolation(
                    'Les champs seoTitle et seoDescription doivent être fournis ensemble.',
                );
            }
            $seo = SeoMetadata::create($command->seoTitle, $command->seoDescription);
        }

        $author = null;
        if ($command->authorName !== null || $command->authorType !== null) {
            if ($command->authorName === null || $command->authorType === null) {
                throw new ArticleInvariantViolation(
                    'Les champs authorName et authorType doivent être fournis ensemble.',
                );
            }
            $author = $command->authorType === AuthorType::Person
                ? Author::person($command->authorName)
                : Author::organization($command->authorName);
        }

        if ($command->bodyMarkdown !== null) {
            $this->markdownValidator->validate($command->bodyMarkdown);
        }

        if ($command->createdAt !== null) {
            if ($command->createdAt > $now) {
                throw new ArticleInvariantViolation(
                    'La date de création ne peut pas être postérieure à l\'instant présent.',
                );
            }
            if ($article->publishedAt() !== null && $command->createdAt > $article->publishedAt()) {
                throw new ArticleInvariantViolation(
                    'La date de création ne peut pas être postérieure à la date de première publication.',
                );
            }
        }

        // 2) L'agrégat vérifiera son propre statut au premier appel de
        //    mutation. On délègue au domaine — cohérent avec la garantie
        //    « éditable seulement si Draft ».
        $before = $article->updatedAt();

        if ($command->title !== null) {
            $article->changeTitle($command->title, $now);
        }
        if ($command->excerpt !== null) {
            $article->changeExcerpt($command->excerpt, $now);
        }
        if ($command->bodyMarkdown !== null) {
            $article->rewriteBody($command->bodyMarkdown, $now);
        }
        if ($seo !== null) {
            $article->changeSeo($seo, $now);
        }
        if ($author !== null) {
            $article->changeAuthor($author, $now);
        }
        if ($command->expertises !== null) {
            $article->changeExpertises($command->expertises, $now);
        }
        if ($command->createdAt !== null) {
            $article->changeCreatedAt($command->createdAt, $now);
        }

        $mutated = $article->updatedAt() !== $before;

        if ($mutated) {
            $this->entityManager->flush();

            return UpdateDraftArticleResult::mutated($article);
        }

        return UpdateDraftArticleResult::unchanged($article);
    }
}
