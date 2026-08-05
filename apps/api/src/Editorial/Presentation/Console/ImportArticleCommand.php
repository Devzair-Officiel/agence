<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Console;

use App\Editorial\Application\Command\ImportArticleFromMarkdown;
use App\Editorial\Application\Command\ImportArticleFromMarkdownHandler;
use App\Editorial\Application\Markdown\MarkdownParseException;
use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * `bin/console app:editorial:import <path> [--dry-run]`.
 *
 * Aucun output HTML : cette commande est un outil Ops/éditeur, exécutée
 * dans le conteneur `api`. Le fichier `<path>` est un chemin dans le
 * système de fichiers du conteneur.
 *
 * Codes de sortie :
 * - 0 : succès (article importé OU dry-run valide) ;
 * - 1 : échec (parseur, validateur, slug déjà présent, invariant refusé).
 */
#[AsCommand(
    name: 'app:editorial:import',
    description: 'Importe un article Markdown (front matter YAML + corps) en tant que brouillon.',
)]
final class ImportArticleCommand extends Command
{
    public function __construct(
        private readonly ImportArticleFromMarkdownHandler $handler,
        private readonly LoggerInterface $editorialLogger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Chemin absolu ou relatif vers le fichier .md à importer.',
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Valide le fichier sans écrire en base.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pathArg = $input->getArgument('path');
        if (!\is_string($pathArg) || $pathArg === '') {
            $io->error('Le chemin fourni est vide.');

            return Command::FAILURE;
        }

        $dryRun = (bool) $input->getOption('dry-run');
        $command = new ImportArticleFromMarkdown($pathArg, $dryRun);

        try {
            $result = ($this->handler)($command);
        } catch (MarkdownParseException $e) {
            $io->error(\sprintf('Parseur Markdown : %s', $e->getMessage()));
            $this->editorialLogger->warning('editorial.import.parse_error', [
                'path' => $pathArg,
                'message' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        } catch (MarkdownValidationException $e) {
            $io->error(\sprintf('Validation : %s', $e->getMessage()));
            if ($e->violations !== []) {
                $io->listing($e->violations);
            }
            $this->editorialLogger->warning('editorial.import.validation_error', [
                'path' => $pathArg,
                'violations' => $e->violations,
            ]);

            return Command::FAILURE;
        } catch (ArticleInvariantViolation $e) {
            $io->error(\sprintf('Invariant du domaine : %s', $e->getMessage()));
            $this->editorialLogger->warning('editorial.import.invariant', [
                'path' => $pathArg,
                'message' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }

        $article = $result->article;

        if ($result->persisted) {
            $io->success(\sprintf(
                'Article importé en brouillon (slug "%s", id %s).',
                $article->slug()->value(),
                $article->id()->toRfc4122(),
            ));
            $this->editorialLogger->info('editorial.import.success', [
                'slug' => $article->slug()->value(),
                'article_id' => $article->id()->toRfc4122(),
            ]);
        } else {
            $io->success(\sprintf(
                'Dry-run OK : le fichier serait importé comme brouillon "%s".',
                $article->slug()->value(),
            ));
            $this->editorialLogger->info('editorial.import.dry_run', [
                'path' => $pathArg,
                'slug' => $article->slug()->value(),
            ]);
        }

        $io->definitionList(
            ['Slug' => $article->slug()->value()],
            ['Titre' => $article->title()],
            ['Résumé' => $article->excerpt()],
            ['Auteur' => \sprintf('%s (%s)', $article->author()->name(), $article->author()->type()->value)],
            ['Piliers' => implode(', ', array_map(
                static fn ($e): string => $e->value,
                $article->expertises(),
            ))],
        );

        return Command::SUCCESS;
    }
}
