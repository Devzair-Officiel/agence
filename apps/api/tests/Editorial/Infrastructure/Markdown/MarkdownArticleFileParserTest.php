<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Infrastructure\Markdown;

use App\Editorial\Application\Markdown\MarkdownParseException;
use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Infrastructure\Markdown\MarkdownArticleFileParser;
use PHPUnit\Framework\TestCase;

final class MarkdownArticleFileParserTest extends TestCase
{
    private MarkdownArticleFileParser $parser;

    protected function setUp(): void
    {
        $this->parser = new MarkdownArticleFileParser();
    }

    public function testParsesWellFormedFrontMatter(): void
    {
        $raw = self::validArticle();
        $result = $this->parser->parseString($raw);

        $fm = $result['frontMatter'];
        self::assertSame('exemple-article', $fm->slug->value());
        self::assertSame('Guide interne sur la structuration éditoriale', $fm->title);
        self::assertSame('Devzair', $fm->author->name());
        self::assertSame(AuthorType::Organization, $fm->author->type());
        self::assertSame(
            [ExpertiseIdentifier::Concevoir, ExpertiseIdentifier::Visibilite],
            $fm->expertises,
        );
        self::assertStringContainsString('## Introduction', $result['body']);
    }

    public function testRejectsMissingFrontMatter(): void
    {
        $this->expectException(MarkdownParseException::class);
        $this->expectExceptionMessageMatches('/Front matter YAML manquant/');

        $this->parser->parseString("Un simple corps sans YAML.\n");
    }

    public function testRejectsUnclosedFrontMatter(): void
    {
        $this->expectException(MarkdownParseException::class);
        $this->expectExceptionMessageMatches('/Front matter YAML non termin/');

        $this->parser->parseString("---\nslug: x\n\nBody sans fin de YAML.\n");
    }

    public function testRejectsEmptyBody(): void
    {
        $this->expectException(MarkdownParseException::class);
        $this->expectExceptionMessageMatches('/corps Markdown est vide/');

        $this->parser->parseString(<<<'MD'
        ---
        slug: exemple-article
        title: Un titre suffisamment long pour respecter la borne minimale
        excerpt: Un résumé assez long pour dépasser la borne minimale imposée par le domaine.
        seo:
          title: Un titre SEO suffisamment long pour dépasser la borne minimale
          description: Description SEO assez longue pour dépasser la borne minimale imposée par les invariants du domaine.
        author:
          name: Devzair
          type: organization
        expertises:
          - concevoir
        ---

        MD);
    }

    public function testRejectsBom(): void
    {
        $this->expectException(MarkdownParseException::class);
        $this->expectExceptionMessageMatches('/BOM UTF-8/');

        $this->parser->parseString("\xEF\xBB\xBF---\nslug: x\n---\nBody.\n");
    }

    public function testRejectsNonUtf8(): void
    {
        $this->expectException(MarkdownParseException::class);
        $this->expectExceptionMessageMatches('/UTF-8/');

        $latin1 = mb_convert_encoding(self::validArticle(), 'ISO-8859-1', 'UTF-8');
        \assert(\is_string($latin1));
        $this->parser->parseString($latin1);
    }

    public function testRejectsPublishedAtInFrontMatter(): void
    {
        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('/publishedAt/');

        $this->parser->parseString(<<<'MD'
        ---
        slug: exemple-article
        title: Un titre suffisamment long pour respecter la borne minimale
        excerpt: Un résumé assez long pour dépasser la borne minimale imposée par le domaine.
        publishedAt: 2026-08-04T09:00:00Z
        seo:
          title: Un titre SEO suffisamment long pour dépasser la borne minimale
          description: Description SEO assez longue pour dépasser la borne minimale imposée par les invariants du domaine.
        author:
          name: Devzair
          type: organization
        expertises:
          - concevoir
        ---
        Body.
        MD);
    }

    public function testRejectsUnknownRootField(): void
    {
        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('/Champ racine inconnu/');

        $this->parser->parseString(<<<'MD'
        ---
        slug: exemple-article
        title: Un titre suffisamment long pour respecter la borne minimale
        excerpt: Un résumé assez long pour dépasser la borne minimale imposée par le domaine.
        surprise: valeur
        seo:
          title: Un titre SEO suffisamment long pour dépasser la borne minimale
          description: Description SEO assez longue pour dépasser la borne minimale imposée par les invariants du domaine.
        author:
          name: Devzair
          type: organization
        expertises:
          - concevoir
        ---
        Body.
        MD);
    }

    public function testRejectsUnknownExpertise(): void
    {
        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('/identifiant inconnu/');

        $this->parser->parseString(<<<'MD'
        ---
        slug: exemple-article
        title: Un titre suffisamment long pour respecter la borne minimale
        excerpt: Un résumé assez long pour dépasser la borne minimale imposée par le domaine.
        seo:
          title: Un titre SEO suffisamment long pour dépasser la borne minimale
          description: Description SEO assez longue pour dépasser la borne minimale imposée par les invariants du domaine.
        author:
          name: Devzair
          type: organization
        expertises:
          - concevoir
          - super-power
        ---
        Body.
        MD);
    }

    public function testRejectsFileTooLarge(): void
    {
        $this->expectException(MarkdownParseException::class);
        $this->expectExceptionMessageMatches('/trop volumineux/');

        $tmp = tempnam(sys_get_temp_dir(), 'md-oversize-');
        \assert(\is_string($tmp));
        try {
            file_put_contents($tmp, str_repeat('x', 524_289));
            $this->parser->parseFile($tmp);
        } finally {
            @unlink($tmp);
        }
    }

    public function testRejectsUnknownFile(): void
    {
        $this->expectException(MarkdownParseException::class);
        $this->expectExceptionMessageMatches('/introuvable/');

        $this->parser->parseFile('/tmp/does-not-exist-'.uniqid('', true).'.md');
    }

    public function testRejectsInvalidAuthorType(): void
    {
        $this->expectException(MarkdownValidationException::class);
        $this->expectExceptionMessageMatches('/author\.type/');

        $this->parser->parseString(<<<'MD'
        ---
        slug: exemple-article
        title: Un titre suffisamment long pour respecter la borne minimale
        excerpt: Un résumé assez long pour dépasser la borne minimale imposée par le domaine.
        seo:
          title: Un titre SEO suffisamment long pour dépasser la borne minimale
          description: Description SEO assez longue pour dépasser la borne minimale imposée par les invariants du domaine.
        author:
          name: Devzair
          type: robot
        expertises:
          - concevoir
        ---
        Body.
        MD);
    }

    private static function validArticle(): string
    {
        return <<<'MD'
        ---
        slug: exemple-article
        title: Guide interne sur la structuration éditoriale
        excerpt: Un aperçu synthétique du guide, assez long pour dépasser la borne minimale imposée par le domaine.
        seo:
          title: Structurer un projet éditorial durable pour son agence
          description: "Guide court sur la structuration éditoriale : gouvernance, workflow, mesure. Adapté à une agence de taille humaine."
        author:
          name: Devzair
          type: organization
        expertises:
          - concevoir
          - visibilite
        ---
        ## Introduction

        Un paragraphe assez long pour être crédible et pour tester le parseur.
        MD;
    }
}
