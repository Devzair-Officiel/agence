<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\Clock\ClockInterface;
use App\Editorial\Infrastructure\Markdown\MarkdownArticleFileParser;
use App\Editorial\Infrastructure\Markdown\MarkdownContentValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Import d'un article depuis un fichier Markdown vers la base éditoriale.
 *
 * Pipeline strict :
 * 1. Parseur : lit le fichier, valide encodage/taille, extrait YAML+corps ;
 * 2. Validateur : refuse HTML brut et URLs à schéma dangereux ;
 * 3. Détection de collision : si un article existe déjà pour ce slug
 *    (Draft, Published, Archived), on refuse l'import — pas de logique
 *    `--update` en Phase 8B1 (le contrat d'import est « create only »
 *    pour préserver l'invariant : un slug = un article, un import = un
 *    nouvel article, une modification = une intervention manuelle
 *    explicite en base ou CLI dédiée future) ;
 * 4. Construction du brouillon via `Article::createDraft()` ;
 * 5. Persistance + flush — sauf en mode `--dry-run` où on retourne le
 *    résultat sans écrire.
 *
 * L'article importé est TOUJOURS créé en statut Draft. La publication est
 * une action explicite via `app:editorial:publish`.
 */
final class ImportArticleFromMarkdownHandler
{
    public function __construct(
        private readonly MarkdownArticleFileParser $parser,
        private readonly MarkdownContentValidator $validator,
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(ImportArticleFromMarkdown $command): ImportArticleResult
    {
        $parsed = $this->parser->parseFile($command->filePath);
        $frontMatter = $parsed['frontMatter'];
        $body = $parsed['body'];

        $this->validator->validate($body);

        $existing = $this->repository->findBySlug($frontMatter->slug);
        if ($existing !== null) {
            throw new MarkdownValidationException(\sprintf(
                'Slug "%s" déjà présent en base (statut : %s). L\'import ne met pas à jour un article existant.',
                $frontMatter->slug->value(),
                $existing->status()->value,
            ));
        }

        $now = $this->clock->now();
        $article = Article::createDraft(
            Uuid::v7(),
            $frontMatter->slug,
            $frontMatter->title,
            $frontMatter->excerpt,
            $body,
            $frontMatter->seo,
            $frontMatter->author,
            $frontMatter->expertises,
            $now,
        );

        if ($command->dryRun) {
            return ImportArticleResult::dryRun($article);
        }

        $this->repository->save($article);
        $this->entityManager->flush();

        return ImportArticleResult::persisted($article);
    }
}
