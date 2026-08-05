<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\ImportArticleFromMarkdown;
use App\Editorial\Application\Command\ImportArticleFromMarkdownHandler;
use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Infrastructure\Markdown\MarkdownArticleFileParser;
use App\Editorial\Infrastructure\Markdown\MarkdownContentValidator;
use App\Editorial\Infrastructure\Markdown\MarkdownSecurityPolicy;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use App\Tests\Editorial\Support\MarkdownFixture;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ImportArticleFromMarkdownHandlerTest extends TestCase
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

    public function testImportsFileAsDraftAndFlushes(): void
    {
        $path = $this->fixture->writeValid('nouveau.md', 'nouveau-guide');
        $repository = new InMemoryArticleRepository();
        $em = $this->entityManagerExpectingFlush();
        $handler = $this->handler($repository, $em);

        $result = $handler(new ImportArticleFromMarkdown($path, dryRun: false));

        self::assertTrue($result->persisted);
        self::assertSame(ArticleStatus::Draft, $result->article->status());
        self::assertSame('nouveau-guide', $result->article->slug()->value());
        self::assertNotNull($repository->findBySlug($result->article->slug()));
    }

    public function testDryRunDoesNotPersistNorFlush(): void
    {
        $path = $this->fixture->writeValid('preview.md', 'preview-guide');
        $repository = new InMemoryArticleRepository();
        $em = $this->entityManagerExpectingNoFlush();
        $handler = $this->handler($repository, $em);

        $result = $handler(new ImportArticleFromMarkdown($path, dryRun: true));

        self::assertFalse($result->persisted);
        self::assertSame('preview-guide', $result->article->slug()->value());
        self::assertNull($repository->findBySlug($result->article->slug()));
    }

    public function testRefusesDuplicateSlug(): void
    {
        $path = $this->fixture->writeValid('doublon.md', 'doublon-guide');
        $repository = new InMemoryArticleRepository();
        $repository->save((new ArticleBuilder())->withSlug('doublon-guide')->build());
        $em = $this->entityManagerExpectingNoFlush();
        $handler = $this->handler($repository, $em);

        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('/déjà présent/u');

        $handler(new ImportArticleFromMarkdown($path, dryRun: false));
    }

    public function testRefusesFileWithHtml(): void
    {
        $path = $this->fixture->write('avec-html.md', MarkdownFixture::articleWithHtml());
        $repository = new InMemoryArticleRepository();
        $em = $this->entityManagerExpectingNoFlush();
        $handler = $this->handler($repository, $em);

        $this->expectException(MarkdownValidationException::class);

        $handler(new ImportArticleFromMarkdown($path, dryRun: false));
    }

    private function handler(
        InMemoryArticleRepository $repository,
        \Doctrine\ORM\EntityManagerInterface $em,
    ): ImportArticleFromMarkdownHandler {
        return new ImportArticleFromMarkdownHandler(
            new MarkdownArticleFileParser(),
            new MarkdownContentValidator(new MarkdownSecurityPolicy()),
            $repository,
            $em,
            new FixedClock(),
        );
    }
}
