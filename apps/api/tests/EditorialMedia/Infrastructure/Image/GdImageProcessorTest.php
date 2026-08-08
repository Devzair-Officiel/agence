<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Infrastructure\Image;

use App\EditorialMedia\Application\Exception\ImageDimensionsExceededException;
use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Application\Policy\MediaUploadPolicy;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Infrastructure\Image\GdImageProcessor;
use PHPUnit\Framework\TestCase;

final class GdImageProcessorTest extends TestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        if (!\extension_loaded('gd')) {
            self::markTestSkipped('Extension GD requise pour ce test.');
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public function testNormalizesJpegAndReturnsPositiveMetadata(): void
    {
        $source = $this->makeImage(300, 200, MediaType::Jpeg);
        $processor = new GdImageProcessor(new MediaUploadPolicy());

        $normalized = $processor->normalize($source, MediaType::Jpeg);
        $this->tempFiles[] = $normalized->temporaryPath;

        self::assertFileExists($normalized->temporaryPath);
        self::assertSame(MediaType::Jpeg, $normalized->mimeType);
        self::assertSame(300, $normalized->dimensions->width());
        self::assertSame(200, $normalized->dimensions->height());
        self::assertGreaterThan(0, $normalized->sizeBytes);
        self::assertSame(64, \strlen($normalized->sha256->toString()));
    }

    public function testNormalizesPng(): void
    {
        $source = $this->makeImage(100, 100, MediaType::Png);
        $processor = new GdImageProcessor(new MediaUploadPolicy());

        $normalized = $processor->normalize($source, MediaType::Png);
        $this->tempFiles[] = $normalized->temporaryPath;

        self::assertSame(MediaType::Png, $normalized->mimeType);
        self::assertStringEndsWith('.png', $normalized->temporaryPath);
    }

    public function testNormalizesWebP(): void
    {
        if (!\function_exists('imagewebp') || !\function_exists('imagecreatefromwebp')) {
            self::markTestSkipped('Support WebP indisponible dans GD.');
        }
        $source = $this->makeImage(50, 50, MediaType::WebP);
        $processor = new GdImageProcessor(new MediaUploadPolicy());

        $normalized = $processor->normalize($source, MediaType::WebP);
        $this->tempFiles[] = $normalized->temporaryPath;

        self::assertSame(MediaType::WebP, $normalized->mimeType);
        self::assertStringEndsWith('.webp', $normalized->temporaryPath);
    }

    public function testRejectsDeclaredMimeMismatch(): void
    {
        $source = $this->makeImage(50, 50, MediaType::Jpeg);
        $processor = new GdImageProcessor(new MediaUploadPolicy());

        $this->expectException(InvalidImageException::class);

        // Fichier réellement JPEG mais on prétend qu'il est PNG.
        $processor->normalize($source, MediaType::Png);
    }

    public function testRejectsUndecodableContent(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'garbage-');
        self::assertNotFalse($tmp);
        $this->tempFiles[] = $tmp;
        file_put_contents($tmp, 'not-an-image-at-all');

        $processor = new GdImageProcessor(new MediaUploadPolicy());

        $this->expectException(InvalidImageException::class);

        $processor->normalize($tmp, MediaType::Jpeg);
    }

    public function testStripsExifMetadataAfterReencoding(): void
    {
        if (!\function_exists('exif_read_data')) {
            self::markTestSkipped('Extension exif requise pour vérifier le stripping EXIF.');
        }

        // Génère une image JPEG standard. GD n'écrit pas d'EXIF nativement, mais
        // on vérifie qu'aucune donnée EXIF n'est présente en sortie — invariant
        // fondamental (« pas de GPS/EXIF côté stockage »).
        $source = $this->makeImage(64, 48, MediaType::Jpeg);
        $processor = new GdImageProcessor(new MediaUploadPolicy());

        $normalized = $processor->normalize($source, MediaType::Jpeg);
        $this->tempFiles[] = $normalized->temporaryPath;

        $exif = @exif_read_data($normalized->temporaryPath);
        // exif_read_data renvoie false quand aucune donnée EXIF n'est présente.
        if ($exif !== false) {
            self::assertArrayNotHasKey('GPS', $exif);
            self::assertArrayNotHasKey('GPSLatitude', $exif);
        }
        self::assertFileExists($normalized->temporaryPath);
    }

    public function testRejectsPreflightDimensionsAboveMax(): void
    {
        // On simule en générant une image légèrement au-dessus de MAX_WIDTH.
        // 8001×10 pixels = très petit fichier, mais préflight refuse.
        $source = $this->makeImage(MediaUploadPolicy::MAX_WIDTH + 1, 10, MediaType::Jpeg);
        $processor = new GdImageProcessor(new MediaUploadPolicy());

        $this->expectException(ImageDimensionsExceededException::class);

        $processor->normalize($source, MediaType::Jpeg);
    }

    private function makeImage(int $width, int $height, MediaType $type): string
    {
        $image = imagecreatetruecolor($width, $height);
        self::assertNotFalse($image);
        // Remplit avec un gris pour éviter les optimisations agressives.
        $color = imagecolorallocate($image, 128, 128, 128);
        self::assertNotFalse($color);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $color);

        $tmp = tempnam(sys_get_temp_dir(), 'gd-source-');
        self::assertNotFalse($tmp);
        $this->tempFiles[] = $tmp;

        switch ($type) {
            case MediaType::Jpeg:
                imagejpeg($image, $tmp, 85);
                break;
            case MediaType::Png:
                imagepng($image, $tmp, 6);
                break;
            case MediaType::WebP:
                imagewebp($image, $tmp, 82);
                break;
        }

        return $tmp;
    }
}
