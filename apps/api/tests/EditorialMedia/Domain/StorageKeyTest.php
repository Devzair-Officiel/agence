<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\StorageKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class StorageKeyTest extends TestCase
{
    public function testForNewAssetBuildsCanonicalPath(): void
    {
        $id = Uuid::fromString('018f8f5d-1234-7abc-9def-abcdef012345');

        $key = StorageKey::forNewAsset($id, MediaType::Jpeg);

        self::assertSame('018f8f5d-1234-7abc-9def-abcdef012345/original.jpg', $key->toString());
    }

    public function testExtensionMatchesMediaType(): void
    {
        $id = Uuid::v7();

        self::assertStringEndsWith('.jpg', StorageKey::forNewAsset($id, MediaType::Jpeg)->toString());
        self::assertStringEndsWith('.png', StorageKey::forNewAsset($id, MediaType::Png)->toString());
        self::assertStringEndsWith('.webp', StorageKey::forNewAsset($id, MediaType::WebP)->toString());
    }

    public function testFromStringAcceptsCanonicalForm(): void
    {
        $raw = '018f8f5d-1234-7abc-9def-abcdef012345/original.png';

        self::assertSame($raw, StorageKey::fromString($raw)->toString());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidProvider(): iterable
    {
        yield 'traversal' => ['../etc/passwd'];
        yield 'no uuid' => ['not-a-uuid/original.jpg'];
        yield 'wrong extension' => ['018f8f5d-1234-7abc-9def-abcdef012345/original.gif'];
        yield 'uppercase extension' => ['018f8f5d-1234-7abc-9def-abcdef012345/original.JPG'];
        yield 'wrong filename' => ['018f8f5d-1234-7abc-9def-abcdef012345/other.jpg'];
        yield 'nested traversal' => ['018f8f5d-1234-7abc-9def-abcdef012345/../original.jpg'];
        yield 'absolute' => ['/018f8f5d-1234-7abc-9def-abcdef012345/original.jpg'];
        yield 'trailing slash' => ['018f8f5d-1234-7abc-9def-abcdef012345/original.jpg/'];
        yield 'null byte' => ["018f8f5d-1234-7abc-9def-abcdef012345/original.jpg\0"];
        yield 'empty' => [''];
    }

    #[DataProvider('invalidProvider')]
    public function testFromStringRejectsMalformed(string $raw): void
    {
        $this->expectException(EditorialMediaInvariantViolation::class);

        StorageKey::fromString($raw);
    }
}
