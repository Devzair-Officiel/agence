<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Image;

use App\EditorialMedia\Application\Exception\ImageDimensionsExceededException;
use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Application\Image\ImageProcessorInterface;
use App\EditorialMedia\Application\Image\NormalizedImage;
use App\EditorialMedia\Application\Policy\MediaUploadPolicy;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;

/**
 * Décode l'image via GD, ré-encode dans un fichier temporaire — l'opération
 * de ré-encodage supprime automatiquement toutes les métadonnées EXIF, GPS,
 * commentaires et profils ICC (pertes acceptables pour un média éditorial).
 *
 * Défense en profondeur :
 *   - Bornes de dimensions vérifiées AVANT allocation GD, à partir des
 *     dimensions retournées par `getimagesize` (lecture d'en-tête uniquement).
 *     Cela évite qu'une image de 40 000×40 000 pixels n'épuise la mémoire
 *     pendant le décodage.
 *   - Chaque ressource GD est libérée dans un `finally`.
 *   - Le fichier temporaire créé est laissé au handler, qui décide de le
 *     déplacer ou de le supprimer.
 *
 * Qualité d'encodage : JPEG 85 (bon compromis taille/qualité), PNG niveau 6
 * (défaut GD, sans perte), WebP 82. Ces réglages sont volontairement fixes —
 * pas de configuration exposée en 9A.
 */
final class GdImageProcessor implements ImageProcessorInterface
{
    private const JPEG_QUALITY = 85;
    private const PNG_COMPRESSION = 6;
    private const WEBP_QUALITY = 82;

    public function __construct(
        private readonly MediaUploadPolicy $policy,
    ) {
    }

    public function normalize(string $sourcePath, MediaType $declaredType): NormalizedImage
    {
        $info = @getimagesize($sourcePath);
        if ($info === false) {
            throw InvalidImageException::undecodable();
        }

        [$declaredWidth, $declaredHeight] = [$info[0], $info[1]];
        $detectedType = self::mediaTypeFromGdImageType((int) $info[2]);
        if ($detectedType === null || $detectedType !== $declaredType) {
            throw InvalidImageException::undecodable();
        }

        $preflightDimensions = MediaDimensions::of($declaredWidth, $declaredHeight);
        $this->policy->assertDimensionsAccepted($preflightDimensions);

        $image = self::decode($sourcePath, $declaredType);

        $temporaryPath = self::createTempFile($declaredType);
        try {
            self::encode($image, $temporaryPath, $declaredType);
        } catch (\Throwable $e) {
            @unlink($temporaryPath);
            unset($image);
            throw $e;
        }
        unset($image);

        $width = $preflightDimensions->width();
        $height = $preflightDimensions->height();

        clearstatcache(true, $temporaryPath);
        $sizeBytes = @filesize($temporaryPath);
        if ($sizeBytes === false || $sizeBytes <= 0) {
            @unlink($temporaryPath);
            throw InvalidImageException::undecodable();
        }

        $hash = @hash_file('sha256', $temporaryPath);
        if ($hash === false) {
            @unlink($temporaryPath);
            throw InvalidImageException::undecodable();
        }

        \assert($temporaryPath !== '');

        return new NormalizedImage(
            temporaryPath: $temporaryPath,
            mimeType: $declaredType,
            dimensions: MediaDimensions::of($width, $height),
            sizeBytes: $sizeBytes,
            sha256: Sha256::fromString($hash),
        );
    }

    private static function mediaTypeFromGdImageType(int $imageType): ?MediaType
    {
        return match ($imageType) {
            \IMAGETYPE_JPEG => MediaType::Jpeg,
            \IMAGETYPE_PNG => MediaType::Png,
            \IMAGETYPE_WEBP => MediaType::WebP,
            default => null,
        };
    }

    /**
     * @return \GdImage
     */
    private static function decode(string $sourcePath, MediaType $type): \GdImage
    {
        $image = match ($type) {
            MediaType::Jpeg => @imagecreatefromjpeg($sourcePath),
            MediaType::Png => @imagecreatefrompng($sourcePath),
            MediaType::WebP => @imagecreatefromwebp($sourcePath),
        };

        if (!$image instanceof \GdImage) {
            throw InvalidImageException::undecodable();
        }

        return $image;
    }

    private static function encode(\GdImage $image, string $destination, MediaType $type): void
    {
        $ok = match ($type) {
            MediaType::Jpeg => imagejpeg($image, $destination, self::JPEG_QUALITY),
            MediaType::Png => imagepng($image, $destination, self::PNG_COMPRESSION),
            MediaType::WebP => imagewebp($image, $destination, self::WEBP_QUALITY),
        };

        if ($ok !== true) {
            throw InvalidImageException::undecodable();
        }
    }

    /**
     * @return non-empty-string
     */
    private static function createTempFile(MediaType $type): string
    {
        $tempDir = sys_get_temp_dir();
        $path = @tempnam($tempDir, 'editorial-media-');
        if ($path === false) {
            throw InvalidImageException::undecodable();
        }
        $final = $path . '.' . $type->extension();
        if (!@rename($path, $final)) {
            @unlink($path);
            throw InvalidImageException::undecodable();
        }
        \assert($final !== '');

        return $final;
    }
}
