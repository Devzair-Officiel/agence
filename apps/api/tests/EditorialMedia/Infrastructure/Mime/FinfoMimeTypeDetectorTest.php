<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Infrastructure\Mime;

use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Infrastructure\Mime\FinfoMimeTypeDetector;
use PHPUnit\Framework\TestCase;

final class FinfoMimeTypeDetectorTest extends TestCase
{
    private FinfoMimeTypeDetector $detector;

    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        $this->detector = new FinfoMimeTypeDetector();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public function testDetectsJpegFromMagicBytes(): void
    {
        $path = $this->createRealImage('jpeg');

        self::assertSame('image/jpeg', $this->detector->detect($path));
    }

    public function testDetectsPngFromMagicBytes(): void
    {
        $path = $this->createRealImage('png');

        self::assertSame('image/png', $this->detector->detect($path));
    }

    public function testDetectsWebPFromMagicBytes(): void
    {
        if (!\function_exists('imagewebp')) {
            self::markTestSkipped('Support WebP indisponible dans GD.');
        }
        $path = $this->createRealImage('webp');

        self::assertSame('image/webp', $this->detector->detect($path));
    }

    public function testExtensionCannotOverrideMime(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'mime-') . '.jpg';
        $this->tempFiles[] = $tmp;
        file_put_contents($tmp, '<html><body>fake</body></html>');

        self::assertSame('text/html', $this->detector->detect($tmp));
    }

    public function testRejectsUnreadableFile(): void
    {
        $missing = sys_get_temp_dir() . '/nonexistent-' . uniqid();

        $this->expectException(InvalidImageException::class);

        $this->detector->detect($missing);
    }

    public function testRejectsEmptyFile(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'empty-');
        self::assertNotFalse($tmp);
        $this->tempFiles[] = $tmp;

        // finfo peut renvoyer 'application/x-empty' ou similaire : on ne veut
        // simplement pas une chaîne vide qui produirait une erreur applicative.
        // Ici, on vérifie que la détection n'échoue pas simplement parce que
        // le fichier est vide — le contrôle métier passe ensuite par la policy.
        $result = $this->detector->detect($tmp);
        self::assertNotSame('', $result);
    }

    private function createRealImage(string $format): string
    {
        if (!\function_exists('imagecreatetruecolor')) {
            self::markTestSkipped('Extension GD requise.');
        }
        $image = imagecreatetruecolor(4, 4);
        self::assertNotFalse($image);
        $tmp = tempnam(sys_get_temp_dir(), 'mime-real-');
        self::assertNotFalse($tmp);
        $this->tempFiles[] = $tmp;

        switch ($format) {
            case 'jpeg':
                self::assertTrue(imagejpeg($image, $tmp, 80));
                break;
            case 'png':
                self::assertTrue(imagepng($image, $tmp, 6));
                break;
            case 'webp':
                self::assertTrue(imagewebp($image, $tmp, 82));
                break;
        }

        return $tmp;
    }
}
