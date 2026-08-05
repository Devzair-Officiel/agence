<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation\Console;

use App\Editorial\Application\Command\ImportArticleFromMarkdownHandler;
use App\Editorial\Infrastructure\Markdown\MarkdownArticleFileParser;
use App\Editorial\Infrastructure\Markdown\MarkdownContentValidator;
use App\Editorial\Infrastructure\Markdown\MarkdownSecurityPolicy;
use App\Editorial\Presentation\Console\ImportArticleCommand;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use App\Tests\Editorial\Support\MarkdownFixture;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ImportArticleCommandTest extends TestCase
{
    use EntityManagerStub;

    private MarkdownFixture $fixture;

    protected function setUp(): void
    {
        $this->fixture = new MarkdownFixture();
    }

    protected function tearDown(): void
    {
        $this->fixture->cleanup();
    }

    public function testImportSuccess(): void
    {
        $path = $this->fixture->writeValid('ok.md', 'cli-import-ok');
        $tester = $this->tester(new InMemoryArticleRepository(), flush: true);

        $status = $tester->execute(['path' => $path]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('brouillon', $tester->getDisplay());
        self::assertStringContainsString('cli-import-ok', $tester->getDisplay());
    }

    public function testDryRunSuccess(): void
    {
        $path = $this->fixture->writeValid('dry.md', 'cli-import-dry');
        $tester = $this->tester(new InMemoryArticleRepository(), flush: false);

        $status = $tester->execute(['path' => $path, '--dry-run' => true]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('Dry-run OK', $tester->getDisplay());
    }

    public function testParserErrorReturnsFailure(): void
    {
        $path = $this->fixture->write('broken.md', "Un simple corps sans YAML.\n");
        $tester = $this->tester(new InMemoryArticleRepository(), flush: false);

        $status = $tester->execute(['path' => $path]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('Parseur Markdown', $tester->getDisplay());
    }

    public function testValidationErrorReturnsFailure(): void
    {
        $path = $this->fixture->write('html.md', MarkdownFixture::articleWithHtml());
        $tester = $this->tester(new InMemoryArticleRepository(), flush: false);

        $status = $tester->execute(['path' => $path]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('Validation', $tester->getDisplay());
    }

    public function testDuplicateSlugReturnsFailure(): void
    {
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('cli-duplicate')->build());
        $path = $this->fixture->writeValid('dup.md', 'cli-duplicate');
        $tester = $this->tester($repository, flush: false);

        $status = $tester->execute(['path' => $path]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('déjà présent', $tester->getDisplay());
    }

    private function tester(InMemoryArticleRepository $repository, bool $flush): CommandTester
    {
        $em = $flush ? $this->entityManagerExpectingFlush() : $this->entityManagerExpectingNoFlush();
        $handler = new ImportArticleFromMarkdownHandler(
            new MarkdownArticleFileParser(),
            new MarkdownContentValidator(new MarkdownSecurityPolicy()),
            $repository,
            $em,
            new FixedClock(),
        );
        $command = new ImportArticleCommand($handler, new NullLogger());

        return new CommandTester($command);
    }
}
