<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Infrastructure\Markdown;

use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Infrastructure\Markdown\MarkdownContentValidator;
use App\Editorial\Infrastructure\Markdown\MarkdownSecurityPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MarkdownContentValidatorTest extends TestCase
{
    private MarkdownContentValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new MarkdownContentValidator(new MarkdownSecurityPolicy());
    }

    public function testAcceptsCleanMarkdown(): void
    {
        $this->validator->validate(<<<'MD'
        ## Introduction

        Un paragraphe avec **gras**, *italique* et un [lien externe](https://example.org).

        - un item
        - un autre

        Un lien mailto : [contact](mailto:hello@example.org) et un tel : [ligne](tel:+33123456789).
        MD);

        // Aucune exception → OK.
        $this->addToAssertionCount(1);
    }

    public function testAcceptsRelativeUrl(): void
    {
        $this->validator->validate('Un [lien interne](/ressources/mon-article).');
        $this->addToAssertionCount(1);
    }

    public function testRejectsHtmlBlock(): void
    {
        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('/HTML brut \(bloc\)/');

        $this->validator->validate(<<<'MD'
        Un paragraphe.

        <div class="callout">Contenu HTML brut</div>

        Une suite.
        MD);
    }

    public function testRejectsInlineHtml(): void
    {
        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('/HTML brut \(inline\)/');

        $this->validator->validate('Un paragraphe avec <span>du HTML inline</span>.');
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function dangerousUrlProvider(): iterable
    {
        yield 'javascript: link' => ['[cliquez](javascript:alert(1))'];
        yield 'data: link' => ['[image](data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==)'];
        yield 'vbscript: link' => ['[legacy](vbscript:msgbox("x"))'];
        yield 'file: link' => ['[disque](file:///etc/passwd)'];
        yield 'javascript: image' => ['![alt](javascript:alert(1))'];
    }

    #[DataProvider('dangerousUrlProvider')]
    public function testRejectsDangerousUrls(string $markdown): void
    {
        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('#Schéma d\'URL "[^"]+" interdit#u');

        $this->validator->validate($markdown);
    }

    public function testRejectsEmptyUrl(): void
    {
        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('/URL vide interdite/');

        $this->validator->validate('Un [lien]().');
    }

    public function testAggregatesMultipleViolationsIntoSingleException(): void
    {
        try {
            $this->validator->validate(<<<'MD'
            <div>HTML block</div>

            Un [lien javascript](javascript:alert(1)) et un <span>inline</span>.
            MD);
            self::fail('Attendu MarkdownValidationException.');
        } catch (MarkdownValidationException $e) {
            self::assertGreaterThanOrEqual(2, \count($e->violations));
        }
    }
}
