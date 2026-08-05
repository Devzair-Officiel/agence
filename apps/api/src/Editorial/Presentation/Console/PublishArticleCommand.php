<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Console;

use App\Editorial\Application\Command\PublishArticleBySlug;
use App\Editorial\Application\Command\PublishArticleBySlugHandler;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * `bin/console app:editorial:publish <slug> [--published-at=YYYY-MM-DDTHH:MM:SSZ]`.
 *
 * Publie un article existant (Draft ou Archived → Published) à la date
 * fournie ou à l'instant présent. La date doit être un ISO 8601 valide
 * avec fuseau explicite (`Z` ou `+HH:MM`) ; l'heure « naïve » (sans fuseau)
 * est refusée pour éviter toute ambiguïté serveur/éditeur.
 */
#[AsCommand(
    name: 'app:editorial:publish',
    description: 'Publie un article existant à partir de son slug.',
)]
final class PublishArticleCommand extends Command
{
    public function __construct(
        private readonly PublishArticleBySlugHandler $handler,
        private readonly LoggerInterface $editorialLogger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'slug',
                InputArgument::REQUIRED,
                'Slug de l\'article à publier.',
            )
            ->addOption(
                'published-at',
                null,
                InputOption::VALUE_REQUIRED,
                'Date de publication (ISO 8601 avec fuseau, ex. 2026-08-04T09:00:00Z). Par défaut : maintenant.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $slugArg = $input->getArgument('slug');
        if (!\is_string($slugArg) || $slugArg === '') {
            $io->error('Le slug fourni est vide.');

            return Command::FAILURE;
        }

        try {
            $slug = ArticleSlug::fromString($slugArg);
        } catch (ArticleInvariantViolation $e) {
            $io->error(\sprintf('Slug invalide : %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $publishedAtOption = $input->getOption('published-at');
        $publishedAt = null;
        if ($publishedAtOption !== null) {
            if (!\is_string($publishedAtOption) || $publishedAtOption === '') {
                $io->error('L\'option --published-at ne peut pas être vide.');

                return Command::FAILURE;
            }
            $publishedAt = $this->parseIsoWithTimezone($publishedAtOption);
            if ($publishedAt === null) {
                $io->error(\sprintf(
                    'Date invalide : "%s" doit être un ISO 8601 avec fuseau (ex. 2026-08-04T09:00:00Z).',
                    $publishedAtOption,
                ));

                return Command::FAILURE;
            }
        }

        $command = new PublishArticleBySlug($slug, $publishedAt);

        try {
            $result = ($this->handler)($command);
        } catch (ArticleNotFoundException $e) {
            $io->error(\sprintf('Article introuvable pour le slug "%s".', $slug->value()));
            $this->editorialLogger->warning('editorial.publish.not_found', [
                'slug' => $slug->value(),
            ]);

            return Command::FAILURE;
        } catch (ArticleInvariantViolation $e) {
            $io->error(\sprintf('Refusé : %s', $e->getMessage()));
            $this->editorialLogger->warning('editorial.publish.invariant', [
                'slug' => $slug->value(),
                'message' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }

        $article = $result->article;
        $publishedAtValue = $article->publishedAt();
        \assert($publishedAtValue !== null);

        if ($result->alreadyPublished) {
            $io->warning(\sprintf(
                'Article "%s" déjà publié (depuis %s) — aucune modification.',
                $article->slug()->value(),
                $publishedAtValue->format(\DateTimeInterface::ATOM),
            ));
            $this->editorialLogger->info('editorial.publish.idempotent', [
                'slug' => $article->slug()->value(),
            ]);

            return Command::SUCCESS;
        }

        $io->success(\sprintf(
            'Article "%s" publié (publishedAt = %s).',
            $article->slug()->value(),
            $publishedAtValue->format(\DateTimeInterface::ATOM),
        ));
        $this->editorialLogger->info('editorial.publish.success', [
            'slug' => $article->slug()->value(),
            'article_id' => $article->id()->toRfc4122(),
            'published_at' => $publishedAtValue->format(\DateTimeInterface::ATOM),
        ]);

        return Command::SUCCESS;
    }

    private function parseIsoWithTimezone(string $raw): ?\DateTimeImmutable
    {
        // Refuser explicitement la variante « naïve » (sans Z ni offset).
        // On regarde le dernier caractère et la présence d'un +/- après le T.
        if (!preg_match('/T\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|[+\-]\d{2}:?\d{2})$/', $raw)) {
            return null;
        }

        try {
            return new \DateTimeImmutable($raw);
        } catch (\Exception) {
            return null;
        }
    }
}
