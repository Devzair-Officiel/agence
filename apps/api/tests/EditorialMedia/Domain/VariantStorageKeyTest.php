<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use App\EditorialMedia\Domain\ImageVariant;
use App\EditorialMedia\Domain\VariantStorageKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class VariantStorageKeyTest extends TestCase
{
    public function testForVariantProducesCorrectPath(): void
    {
        $id  = Uuid::fromString('01932b4c-1234-7aaa-bbbb-ccccdddd0001');
        $key = VariantStorageKey::forVariant($id, ImageVariant::Card);
        self::assertSame('01932b4c-1234-7aaa-bbbb-ccccdddd0001/card.webp', $key->toString());
    }

    public function testForVariantHeroProducesCorrectPath(): void
    {
        $id  = Uuid::fromString('01932b4c-1234-7aaa-bbbb-ccccdddd0002');
        $key = VariantStorageKey::forVariant($id, ImageVariant::Hero);
        self::assertSame('01932b4c-1234-7aaa-bbbb-ccccdddd0002/hero.webp', $key->toString());
    }

    public function testFromStringAcceptsValidCard(): void
    {
        $raw = '01932b4c-1234-7aaa-bbbb-ccccdddd0001/card.webp';
        self::assertSame($raw, VariantStorageKey::fromString($raw)->toString());
    }

    public function testFromStringAcceptsValidHero(): void
    {
        $raw = '01932b4c-1234-7aaa-bbbb-ccccdddd0001/hero.webp';
        self::assertSame($raw, VariantStorageKey::fromString($raw)->toString());
    }

    public function testFromStringRejectsOriginalFormat(): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);
        VariantStorageKey::fromString('01932b4c-1234-7aaa-bbbb-ccccdddd0001/original.jpg');
    }

    public function testFromStringRejectsInvalidVariantName(): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);
        VariantStorageKey::fromString('01932b4c-1234-7aaa-bbbb-ccccdddd0001/thumbnail.webp');
    }

    public function testFromStringRejectsPathTraversal(): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);
        VariantStorageKey::fromString('../01932b4c-1234-7aaa-bbbb-ccccdddd0001/card.webp');
    }

    public function testFromStringRejectsEmptyString(): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);
        VariantStorageKey::fromString('');
    }
}
