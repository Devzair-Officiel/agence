<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Console;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleRepositoryInterface;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\Author;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Domain\SeoMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Uid\Uuid;

/**
 * `bin/console app:editorial:e2e-fixtures <load|clear>`.
 *
 * Commande RÉSERVÉE aux environnements `dev` et `test` (attributs
 * `#[When]`) : jamais chargée en `prod`. Elle sert exclusivement à
 * préparer et purger le jeu de fixtures utilisé par la suite Playwright
 * Phase 8B2.
 *
 * Contraintes de sécurité (voir correction 7 du brief) :
 * - aucune commande de suppression globale ; toute suppression est
 *   restreinte au préfixe `e2e-8b2-` (voir `FIXTURE_SLUG_PREFIX`) ;
 * - pas de TRUNCATE ; on utilise un DELETE ciblé pour rester en dessous
 *   du seuil DDL (déclencheurs, migrations vivantes) ;
 * - Playwright n'a jamais accès au socket Docker : cette commande est
 *   orchestrée par un script bash (`scripts/e2e-fixtures.sh`) qui
 *   invoque `docker compose exec` en amont/aval de la suite.
 *
 * Jeu de fixtures (10 articles) :
 * - 7 publiés (`e2e-8b2-published-1..7`) — dates échelonnées, permet de
 *   tester la pagination (per_page=6) : page 1 = 6 récents, page 2 = 1 ;
 * - 1 brouillon (`e2e-8b2-draft-1`) — ne doit jamais apparaître ;
 * - 1 archivé (`e2e-8b2-archived-1`) — ne doit jamais apparaître ;
 * - 1 « futur » (`e2e-8b2-future-1`), `publishedAt = 2035-01-01` — ne
 *   doit jamais apparaître (le repository filtre `publishedAt <= now`).
 */
#[AsCommand(
    name: 'app:editorial:e2e-fixtures',
    description: 'Charge ou purge le jeu de fixtures dédié aux E2E Phase 8B2 (dev/test uniquement).',
)]
#[When(env: 'dev')]
#[When(env: 'test')]
final class E2eFixturesCommand extends Command
{
    /**
     * Préfixe de slug utilisé pour TOUS les articles créés par cette
     * commande. Le DELETE utilise ce préfixe comme borne — tout article
     * dont le slug ne le porte pas est intouché.
     */
    public const FIXTURE_SLUG_PREFIX = 'e2e-8b2-';

    private const ACTION_LOAD = 'load';
    private const ACTION_CLEAR = 'clear';

    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'action',
            InputArgument::REQUIRED,
            \sprintf('Action à exécuter : "%s" ou "%s".', self::ACTION_LOAD, self::ACTION_CLEAR),
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');

        if (!\is_string($action) || ($action !== self::ACTION_LOAD && $action !== self::ACTION_CLEAR)) {
            $io->error(\sprintf(
                'Action inconnue. Utilisez "%s" ou "%s".',
                self::ACTION_LOAD,
                self::ACTION_CLEAR,
            ));

            return Command::FAILURE;
        }

        if ($action === self::ACTION_CLEAR) {
            $this->clear($io);

            return Command::SUCCESS;
        }

        // Load : on purge d'abord pour rendre la commande idempotente.
        $this->clear($io);
        $this->load($io);

        return Command::SUCCESS;
    }

    private function clear(SymfonyStyle $io): void
    {
        // Suppression restreinte au préfixe : jamais de TRUNCATE, jamais
        // de DELETE global. Un test qui poserait accidentellement un
        // slug sans préfixe survit à cet appel.
        $connection = $this->entityManager->getConnection();
        $deleted = $connection->executeStatement(
            'DELETE FROM editorial_article WHERE slug LIKE :prefix',
            ['prefix' => self::FIXTURE_SLUG_PREFIX . '%'],
        );

        $io->writeln(\sprintf(
            '  <info>%d</info> article(s) e2e supprimé(s) (préfixe "%s").',
            $deleted,
            self::FIXTURE_SLUG_PREFIX,
        ));
    }

    private function load(SymfonyStyle $io): void
    {
        $now = new \DateTimeImmutable('2026-08-05T09:00:00+00:00');
        $author = Author::organization('Devzair');
        $pillars = [ExpertiseIdentifier::Concevoir];

        // Sept articles publiés — dates échelonnées de janvier à juillet 2026
        // pour garantir un ordre déterministe (le plus récent d'abord).
        for ($i = 1; $i <= 7; ++$i) {
            $publishedAt = new \DateTimeImmutable(\sprintf('2026-0%d-15T10:00:00+00:00', $i));
            $article = Article::createDraft(
                Uuid::v7(),
                ArticleSlug::fromString(self::FIXTURE_SLUG_PREFIX . 'published-' . $i),
                \sprintf('Article publié E2E n°%d', $i),
                \sprintf('Résumé E2E n°%d — assez long pour satisfaire la borne minimale imposée par le domaine.', $i),
                \sprintf("## Section %d\n\nCorps markdown de test — contenu déterministe pour la suite E2E Phase 8B2.", $i),
                SeoMetadata::create(
                    \sprintf('Titre SEO déterministe pour l\'article E2E Phase 8B2 n°%d', $i),
                    \sprintf('Description SEO courte et honnête pour l\'article E2E n°%d de la Phase 8B2.', $i),
                ),
                $author,
                $pillars,
                $publishedAt,
            );
            $article->publish($publishedAt, $publishedAt);
            $this->repository->save($article);
        }

        // Brouillon — ne doit jamais apparaître dans le listing public.
        $draft = Article::createDraft(
            Uuid::v7(),
            ArticleSlug::fromString(self::FIXTURE_SLUG_PREFIX . 'draft-1'),
            'Brouillon E2E — invisible',
            'Ce brouillon ne doit jamais apparaître dans /ressources ni sur son slug.',
            "## Brouillon\n\nContenu brouillon E2E — ne doit pas être exposé publiquement.",
            SeoMetadata::create('Titre SEO brouillon E2E Phase 8B2 — invisible publiquement', 'Description brouillon E2E qui ne doit jamais apparaître publiquement dans /ressources.'),
            $author,
            $pillars,
            $now,
        );
        $this->repository->save($draft);

        // Archivé — publié puis archivé, ne doit plus apparaître.
        $archivedPublishedAt = new \DateTimeImmutable('2025-06-15T10:00:00+00:00');
        $archived = Article::createDraft(
            Uuid::v7(),
            ArticleSlug::fromString(self::FIXTURE_SLUG_PREFIX . 'archived-1'),
            'Archivé E2E — invisible',
            'Cet article publié puis archivé ne doit jamais apparaître dans /ressources.',
            "## Archivé\n\nContenu archivé E2E — ne doit pas être exposé publiquement.",
            SeoMetadata::create('Titre SEO article archivé E2E Phase 8B2 — invisible', 'Description archivé E2E qui ne doit jamais apparaître publiquement dans /ressources.'),
            $author,
            $pillars,
            $archivedPublishedAt,
        );
        $archived->publish($archivedPublishedAt, $archivedPublishedAt);
        $archived->archive($now);
        $this->repository->save($archived);

        // « Futur » — publishedAt = 2035-01-01. Le domaine accepte car on
        // fournit le même instant comme « maintenant » à la publication.
        // Le repository filtre au moment de la lecture (publishedAt <= now)
        // et masque donc l'article jusqu'en 2035.
        $futureInstant = new \DateTimeImmutable('2035-01-01T10:00:00+00:00');
        $future = Article::createDraft(
            Uuid::v7(),
            ArticleSlug::fromString(self::FIXTURE_SLUG_PREFIX . 'future-1'),
            'Futur E2E — invisible',
            'Article dont la date de publication est en 2035 : ne doit jamais apparaître aujourd\'hui.',
            "## Futur\n\nContenu daté 2035 — ne doit pas être exposé aujourd'hui.",
            SeoMetadata::create('Titre SEO article futur E2E Phase 8B2 — daté 2035', 'Description futur E2E qui ne doit apparaître qu\'à partir de 2035 côté /ressources.'),
            $author,
            $pillars,
            $futureInstant,
        );
        $future->publish($futureInstant, $futureInstant);
        $this->repository->save($future);

        // Un seul flush pour tout le batch — cohérent avec le pattern
        // handler existant (voir ImportArticleFromMarkdownHandler).
        $this->entityManager->flush();

        $io->writeln('  <info>10</info> article(s) e2e chargé(s) (7 publiés + 1 brouillon + 1 archivé + 1 futur).');
    }
}
