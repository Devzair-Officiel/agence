<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Infrastructure\Image;

use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Infrastructure\Image\GdImageVariantProcessor;
use PHPUnit\Framework\TestCase;

/**
 * Tests de `GdImageVariantProcessor` avec de vraies images GD en mémoire.
 *
 * Chaque cas vérifie :
 *   1. Les dimensions de sortie sont exactement 16:9 : `width * 9 === height * 16`.
 *   2. Aucun upscale : aucune dimension de sortie ne dépasse la source.
 *   3. La sortie est décodable en WebP (imagesx/imagesy > 0).
 */
final class GdImageVariantProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        if (!\extension_loaded('gd')) {
            self::markTestSkipped('Extension GD requise.');
        }
    }

    /**
     * @dataProvider dimensionProvider
     */
    public function testExact169Ratio(
        int $srcW,
        int $srcH,
        int $expectedCardW,
        int $expectedCardH,
        int $expectedHeroW,
        int $expectedHeroH,
    ): void {
        $source = $this->createJpeg($srcW, $srcH);

        $processor = new GdImageVariantProcessor();
        $result    = $processor->generateVariants($source, MediaType::Jpeg);

        try {
            // Ratio exact 16:9 — vérification arithmétique stricte
            self::assertSame(
                0,
                $result->card->width * 9 - $result->card->height * 16,
                \sprintf('card %dx%d n\'est pas exactement 16:9.', $result->card->width, $result->card->height),
            );
            self::assertSame(
                0,
                $result->hero->width * 9 - $result->hero->height * 16,
                \sprintf('hero %dx%d n\'est pas exactement 16:9.', $result->hero->width, $result->hero->height),
            );

            // Dimensions attendues
            self::assertSame($expectedCardW, $result->card->width);
            self::assertSame($expectedCardH, $result->card->height);
            self::assertSame($expectedHeroW, $result->hero->width);
            self::assertSame($expectedHeroH, $result->hero->height);

            // No upscale : sortie ≤ source (on compare la dimension la plus contraignante)
            self::assertLessThanOrEqual($srcW, $result->card->width);
            self::assertLessThanOrEqual($srcH, $result->card->height);
            self::assertLessThanOrEqual($srcW, $result->hero->width);
            self::assertLessThanOrEqual($srcH, $result->hero->height);

            // Décodabilité WebP réelle
            self::assertIsDecodableWebp($result->card->temporaryPath);
            self::assertIsDecodableWebp($result->hero->temporaryPath);
        } finally {
            @unlink($source);
            @unlink($result->card->temporaryPath);
            @unlink($result->hero->temporaryPath);
        }
    }

    /**
     * @return array<string, array{int, int, int, int, int, int}>
     */
    public static function dimensionProvider(): array
    {
        return [
            // Source 1920×1080 — exactement 16:9
            'exact_16_9_1920x1080' => [1920, 1080, 768, 432, 1600, 900],

            // Source 800×600 — plus haute que 16:9, crop haut/bas
            // crop = 800×450, k_source=50, card: min(50,48)=48 → 768×432, hero: min(50,100)=50 → 800×450
            'taller_800x600' => [800, 600, 768, 432, 800, 450],

            // Source 600×400 — plus haute que 16:9
            // crop = 600×337, k=min(37,37)=37, card=hero=592×333
            'small_600x400' => [600, 400, 592, 333, 592, 333],

            // Source 300×200 — très petite
            // crop = 300×168, k=min(18,18)=18, card=hero=288×162
            'tiny_300x200' => [300, 200, 288, 162, 288, 162],

            // Source 2560×1080 — plus large que 16:9 (ultra-wide)
            // crop: sh=1080, sw=intdiv(1080*16,9)=1920, offX=intdiv((2560-1920)/2)=320
            // k_source=min(120,120)=120, card=768×432, hero=1600×900
            'ultrawide_2560x1080' => [2560, 1080, 768, 432, 1600, 900],
        ];
    }

    private static function assertIsDecodableWebp(string $path): void
    {
        $img = @imagecreatefromwebp($path);
        self::assertInstanceOf(\GdImage::class, $img, \sprintf('%s n\'est pas un WebP décodable.', $path));
        self::assertGreaterThan(0, imagesx($img));
        imagedestroy($img);
    }

    private function createJpeg(int $w, int $h): string
    {
        $img  = imagecreatetruecolor($w, $h);
        self::assertInstanceOf(\GdImage::class, $img);
        $blue = imagecolorallocate($img, 0, 102, 204);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $blue);

        $tmp = tempnam(sys_get_temp_dir(), 'gd-variant-src-') . '.jpg';
        imagejpeg($img, $tmp, 85);
        imagedestroy($img);

        return $tmp;
    }
}
