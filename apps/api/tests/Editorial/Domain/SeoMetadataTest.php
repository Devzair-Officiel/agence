<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Domain;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\SeoMetadata;
use PHPUnit\Framework\TestCase;

final class SeoMetadataTest extends TestCase
{
    public function testAcceptsValidTitleAndDescription(): void
    {
        $seo = SeoMetadata::create(
            'Structurer un projet éditorial durable pour son agence',
            'Guide court sur la structuration éditoriale : gouvernance, workflow, mesure. Adapté aux agences de taille humaine.',
        );

        self::assertSame(
            'Structurer un projet éditorial durable pour son agence',
            $seo->title(),
        );
        self::assertStringContainsString('gouvernance', $seo->description());
    }

    public function testRejectsTitleTooShort(): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        SeoMetadata::create(
            'Trop court',
            'Description longue et valide qui contient au moins 70 caractères pour tester la description minimale sans souci.',
        );
    }

    public function testRejectsTitleTooLong(): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        SeoMetadata::create(
            str_repeat('A', 71),
            'Description longue et valide qui contient au moins 70 caractères pour tester la description minimale sans souci.',
        );
    }

    public function testRejectsDescriptionTooShort(): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        SeoMetadata::create(
            'Structurer un projet éditorial durable pour son agence',
            'Trop court.',
        );
    }
}
