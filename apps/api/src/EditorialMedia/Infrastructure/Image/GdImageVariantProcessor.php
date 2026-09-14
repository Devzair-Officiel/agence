<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Image;

use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Application\Image\ImageVariantProcessorInterface;
use App\EditorialMedia\Application\Image\ImageVariantResult;
use App\EditorialMedia\Application\Image\ImageVariantsResult;
use App\EditorialMedia\Domain\ImageVariant;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;

/**
 * Génère les variants WebP card (≤768×432) et hero (≤1600×900) à partir
 * du fichier normalisé (Phase 9C).
 *
 * Algorithme de dimensionnement — ratio 16:9 exact, sans upscale :
 *
 *   1. Crop centré : ramène la source au plus grand rectangle 16:9 possible.
 *      - Source plus large que 16:9 → rogner les côtés (conserver la hauteur).
 *      - Source plus haute que 16:9 → rogner haut/bas (conserver la largeur).
 *      - Offset centré ; si impair, le pixel supplémentaire va vers le bas/droite.
 *
 *   2. Scaling sans upscale : on cherche le plus grand entier k tel que
 *      `16k ≤ crop_w` et `9k ≤ crop_h`, puis on retient `k ≤ k_cap`.
 *      - Card : k_cap = 48  → plafond nominal 768×432
 *      - Hero : k_cap = 100 → plafond nominal 1600×900
 *      Les dimensions de sortie sont exactement `16k × 9k` (ratio 16:9 strict).
 *
 *   3. `imagecopyresampled` réalise crop + resize en un seul appel.
 *
 * Mémoire :
 *   - L'image source est décodée une seule fois et conservée le temps des
 *     deux variants.
 *   - Chaque bitmap de destination est libéré immédiatement après encodage
 *     WebP. La source est libérée en dernier.
 *   - Mémoire de pointe estimée (source 40 MP) : ~160 MiB source +
 *     ~6 MiB hero = ~166 MiB < 256 MiB (memory_limit actuel).
 *     À valider par test avant tout déploiement d'images > 20 MP.
 *
 * Qualité WebP : 82 (cohérent avec `GdImageProcessor`).
 */
final class GdImageVariantProcessor implements ImageVariantProcessorInterface
{
    private const WEBP_QUALITY = 82;

    public function generateVariants(string $normalizedPath, MediaType $type): ImageVariantsResult
    {
        $src = self::decode($normalizedPath, $type);

        $cardTmp = null;
        $heroTmp = null;
        try {
            $cardTmp = self::createTempWebp();
            $cardResult = $this->generateOne($src, ImageVariant::Card, $cardTmp);

            $heroTmp = self::createTempWebp();
            $heroResult = $this->generateOne($src, ImageVariant::Hero, $heroTmp);
        } catch (\Throwable $e) {
            if ($cardTmp !== null && is_file($cardTmp)) {
                @unlink($cardTmp);
            }
            if ($heroTmp !== null && is_file($heroTmp)) {
                @unlink($heroTmp);
            }
            throw $e;
        } finally {
            imagedestroy($src);
        }

        return new ImageVariantsResult($cardResult, $heroResult);
    }

    private function generateOne(\GdImage $src, ImageVariant $variant, string $tmpPath): ImageVariantResult
    {
        [$offX, $offY, $cropW, $cropH, $outW, $outH] = self::computeDimensions($src, $variant);

        $dst = imagecreatetruecolor($outW, $outH);
        if ($dst === false) {
            throw InvalidImageException::undecodable();
        }

        // Préserve le canal alpha pour les sources PNG/WebP avec transparence.
        // Sans ces appels, les pixels transparents sont composités sur le fond
        // noir opaque de imagecreatetruecolor, produisant des artefacts noirs.
        // imagealphablending(false) + imagesavealpha(true) : les valeurs alpha
        // de la source sont copiées telles quelles (pas de composition sur le fond
        // noir). Sans ces appels, les pixels transparents PNG/WebP deviennent noirs.
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);

        try {
            $ok = imagecopyresampled($dst, $src, 0, 0, $offX, $offY, $outW, $outH, $cropW, $cropH);
            if ($ok === false) {
                throw InvalidImageException::undecodable();
            }
            if (!imagewebp($dst, $tmpPath, self::WEBP_QUALITY)) {
                throw InvalidImageException::undecodable();
            }
        } finally {
            imagedestroy($dst);
        }

        clearstatcache(true, $tmpPath);
        $sizeBytes = @filesize($tmpPath);
        if ($sizeBytes === false || $sizeBytes <= 0) {
            throw InvalidImageException::undecodable();
        }

        $hash = @hash_file('sha256', $tmpPath);
        if ($hash === false) {
            throw InvalidImageException::undecodable();
        }

        return new ImageVariantResult(
            temporaryPath: $tmpPath,
            width: $outW,
            height: $outH,
            sizeBytes: $sizeBytes,
            sha256: Sha256::fromString($hash),
        );
    }

    /**
     * Calcule le rectangle de crop et les dimensions de sortie exactement 16:9.
     *
     * @return array{int, int, int, int, int, int} [offX, offY, cropW, cropH, outW, outH]
     */
    private static function computeDimensions(\GdImage $src, ImageVariant $variant): array
    {
        $sw = imagesx($src);
        $sh = imagesy($src);

        $ratio = $sw / $sh;
        $target = 16.0 / 9.0;

        if (abs($ratio - $target) < 1e-9) {
            // Ratio déjà exact — pas de crop
            $cropW = $sw;
            $cropH = $sh;
            $offX  = 0;
            $offY  = 0;
        } elseif ($ratio > $target) {
            // Source plus large que 16:9 : rogner les côtés
            $cropH = $sh;
            $cropW = intdiv($sh * 16, 9);
            $offX  = intdiv($sw - $cropW, 2);
            $offY  = 0;
        } else {
            // Source plus haute que 16:9 : rogner haut/bas
            $cropW = $sw;
            $cropH = intdiv($sw * 9, 16);
            $offX  = 0;
            $offY  = intdiv($sh - $cropH, 2);
        }

        // Plus grand k sans upscale, borné par le plafond du variant
        $k = min(
            intdiv($cropW, 16),
            intdiv($cropH, 9),
            $variant->kCap(),
        );

        if ($k < 1) {
            throw InvalidImageException::undecodable();
        }

        return [$offX, $offY, $cropW, $cropH, 16 * $k, 9 * $k];
    }

    private static function decode(string $path, MediaType $type): \GdImage
    {
        $image = match ($type) {
            MediaType::Jpeg  => @imagecreatefromjpeg($path),
            MediaType::Png   => @imagecreatefrompng($path),
            MediaType::WebP  => @imagecreatefromwebp($path),
        };

        if (!$image instanceof \GdImage) {
            throw InvalidImageException::undecodable();
        }

        return $image;
    }

    /** @return non-empty-string */
    private static function createTempWebp(): string
    {
        $base = @tempnam(sys_get_temp_dir(), 'editorial-variant-');
        if ($base === false) {
            throw InvalidImageException::undecodable();
        }
        $path = $base . '.webp';
        if (!@rename($base, $path)) {
            @unlink($base);
            throw InvalidImageException::undecodable();
        }

        return $path;
    }
}
