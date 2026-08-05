<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation\Console;

use App\Editorial\Application\Command\PublishArticleBySlugHandler;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Presentation\Console\PublishArticleCommand;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class PublishArticleCommandTest extends TestCase
{
    use EntityManagerStub;

    public function testPublishesDraftSuccessfully(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('publish-ok')->build());
        $tester = $this->tester($repository, flush: true, now: '2026-08-04T12:00:00+00:00');

        $status = $tester->execute(['slug' => 'publish-ok']);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('publié', $tester->getDisplay());
        $article = $repository->findBySlug(\App\Editorial\Domain\ArticleSlug::fromString('publish-ok'));
        self::assertNotNull($article);
        self::assertSame(ArticleStatus::Published, $article->status());
    }

    public function testRejectsInvalidPublishedAt(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('publish-naive')->build());
        $tester = $this->tester($repository, flush: false, now: '2026-08-04T12:00:00+00:00');

        $status = $tester->execute([
            'slug' => 'publish-naive',
            '--published-at' => '2026-08-03 09:00:00',
        ]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('Date invalide', $tester->getDisplay());
    }

    public function testRejectsFuturePublishedAt(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('publish-futur')->build());
        $tester = $this->tester($repository, flush: false, now: '2026-08-04T12:00:00+00:00');

        $status = $tester->execute([
            'slug' => 'publish-futur',
            '--published-at' => '2027-01-01T00:00:00Z',
        ]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('Refusé', $tester->getDisplay());
    }

    public function testUnknownSlugReturnsFailure(): void
    {
        $tester = $this->tester(new InMemoryArticleRepository(), flush: false);

        $status = $tester->execute(['slug' => 'slug-fantome']);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('introuvable', $tester->getDisplay());
    }

    public function testAlreadyPublishedIsIdempotent(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('deja-publie-cli')->published()->build());
        $tester = $this->tester($repository, flush: false, now: '2026-12-01T00:00:00+00:00');

        $status = $tester->execute(['slug' => 'deja-publie-cli']);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('déjà publié', $tester->getDisplay());
    }

    public function testMalformedSlugArgumentReturnsFailure(): void
    {
        $tester = $this->tester(new InMemoryArticleRepository(), flush: false);

        $status = $tester->execute(['slug' => 'Slug Invalide!!']);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('Slug invalide', $tester->getDisplay());
    }

    private function tester(
        InMemoryArticleRepository $repository,
        bool $flush,
        string $now = '2026-08-03T10:00:00+00:00',
    ): CommandTester {
        $em = $flush ? $this->entityManagerExpectingFlush() : $this->entityManagerExpectingNoFlush();
        $handler = new PublishArticleBySlugHandler($repository, $em, new FixedClock($now));
        $command = new PublishArticleCommand($handler, new NullLogger());

        return new CommandTester($command);
    }
}
