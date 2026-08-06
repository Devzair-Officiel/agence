<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\Author;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Domain\Exception\ArticleSlugAlreadyExistsException;
use App\Editorial\Domain\SeoMetadata;
use App\Editorial\Infrastructure\Markdown\MarkdownContentValidator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Crée un article `Draft` à partir d'une commande complète.
 *
 * Pipeline strictement ordonné :
 *   1. Construire et valider TOUS les VOs (slug, seo, author, expertises) ;
 *   2. Valider le Markdown (schémas d'URL, HTML brut, taille) ;
 *   3. Pré-vérifier l'unicité du slug via `findBySlug` — court-circuit propre
 *      qui évite la plupart des collisions ;
 *   4. Construire l'agrégat via `Article::createDraft()` ;
 *   5. `save` + `flush` ; toute `UniqueConstraintViolationException` levée
 *      pendant la course concurrente est traduite en
 *      `ArticleSlugAlreadyExistsException` — le vocabulaire Doctrine ne
 *      fuit jamais vers la couche présentation.
 *
 * Atomicité : si l'une des étapes 1 à 3 échoue, aucun `persist` n'a été
 * appelé — l'UoW reste vide, rien à défaire.
 */
final class CreateDraftArticleHandler
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
        private readonly MarkdownContentValidator $markdownValidator,
    ) {
    }

    public function __invoke(CreateDraftArticle $command): CreateDraftArticleResult
    {
        $slug = ArticleSlug::fromString($command->slug);
        $seo = SeoMetadata::create($command->seoTitle, $command->seoDescription);
        $author = $command->authorType === AuthorType::Person
            ? Author::person($command->authorName)
            : Author::organization($command->authorName);

        $this->markdownValidator->validate($command->bodyMarkdown);

        if ($this->repository->findBySlug($slug) !== null) {
            throw ArticleSlugAlreadyExistsException::forSlug($slug->value());
        }

        $article = Article::createDraft(
            Uuid::v7(),
            $slug,
            $command->title,
            $command->excerpt,
            $command->bodyMarkdown,
            $seo,
            $author,
            $command->expertises,
            $this->clock->now(),
        );

        $this->repository->save($article);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            throw ArticleSlugAlreadyExistsException::forSlug($slug->value());
        }

        return new CreateDraftArticleResult($article);
    }
}
