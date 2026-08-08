<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Agrégat racine du domaine EditorialMedia (Phase 9A).
 *
 * Représente uniquement le fichier téléversé + ses métadonnées serveur.
 * Aucune référence à un `Article`, aucune notion d'alt-text contextuel, de
 * caption ou de publication. Ces éléments appartiennent à l'usage du média
 * (Phase 9B) et ne doivent pas polluer cet agrégat.
 *
 * Invariants :
 * - `id` UUID v7 fourni par la couche applicative.
 * - `storageKey` unique et respecte la forme `{uuid}/original.{ext}`.
 * - `originalFilename` non vide, tronqué à 255 caractères (métadonnée
 *   d'affichage uniquement, JAMAIS utilisé comme composante du chemin).
 * - `mimeType` ∈ liste `MediaType` autorisée.
 * - `width`, `height` > 0 (bornes hautes portées par la policy applicative).
 * - `sizeBytes` > 0.
 * - `sha256` : 64 caractères hex (imposé par `Sha256`).
 * - `createdAt` fourni par le `ClockInterface`.
 */
#[ORM\Entity]
#[ORM\Table(name: 'editorial_media_asset')]
#[ORM\UniqueConstraint(name: 'uniq_editorial_media_asset_storage_key', columns: ['storage_key'])]
#[ORM\Index(name: 'idx_editorial_media_asset_created_at', columns: ['created_at', 'id'])]
class MediaAsset
{
    public const ORIGINAL_FILENAME_MAX = 255;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(name: 'storage_key', type: Types::STRING, length: 255, unique: true)]
    private string $storageKey;

    #[ORM\Column(name: 'original_filename', type: Types::STRING, length: self::ORIGINAL_FILENAME_MAX)]
    private string $originalFilename;

    #[ORM\Column(name: 'mime_type', type: Types::STRING, length: 64, enumType: MediaType::class)]
    private MediaType $mimeType;

    #[ORM\Column(name: 'size_bytes', type: Types::BIGINT)]
    private int $sizeBytes;

    #[ORM\Column(name: 'width', type: Types::INTEGER)]
    private int $width;

    #[ORM\Column(name: 'height', type: Types::INTEGER)]
    private int $height;

    #[ORM\Column(name: 'sha256', type: Types::STRING, length: 64)]
    private string $sha256;

    #[ORM\Column(name: 'created_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    private function __construct(
        Uuid $id,
        StorageKey $storageKey,
        string $originalFilename,
        MediaType $mimeType,
        int $sizeBytes,
        MediaDimensions $dimensions,
        Sha256 $sha256,
        \DateTimeImmutable $createdAt,
    ) {
        $safeName = self::sanitizeOriginalFilename($originalFilename);
        self::assertPositive('taille', $sizeBytes);

        $this->id = $id;
        $this->storageKey = $storageKey->toString();
        $this->originalFilename = $safeName;
        $this->mimeType = $mimeType;
        $this->sizeBytes = $sizeBytes;
        $this->width = $dimensions->width();
        $this->height = $dimensions->height();
        $this->sha256 = $sha256->toString();
        $this->createdAt = $createdAt;
    }

    public static function record(
        Uuid $id,
        StorageKey $storageKey,
        string $originalFilename,
        MediaType $mimeType,
        int $sizeBytes,
        MediaDimensions $dimensions,
        Sha256 $sha256,
        \DateTimeImmutable $now,
    ): self {
        return new self($id, $storageKey, $originalFilename, $mimeType, $sizeBytes, $dimensions, $sha256, $now);
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function storageKey(): StorageKey
    {
        return StorageKey::fromString($this->storageKey);
    }

    public function originalFilename(): string
    {
        return $this->originalFilename;
    }

    public function mimeType(): MediaType
    {
        return $this->mimeType;
    }

    public function sizeBytes(): int
    {
        return $this->sizeBytes;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function dimensions(): MediaDimensions
    {
        return MediaDimensions::of($this->width, $this->height);
    }

    public function sha256(): Sha256
    {
        return Sha256::fromString($this->sha256);
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Nettoie un nom d'origine avant persistance :
     * - refuse une chaîne vide (invariant) ;
     * - retire les caractères de contrôle (bruit sans intérêt UI) ;
     * - tronque à 255 caractères max (contrainte colonne).
     *
     * IMPORTANT : ne réécrit pas le nom (garde accents, casse, espaces)
     * — c'est une métadonnée d'affichage. Le chemin réel utilise
     * exclusivement `StorageKey`.
     */
    private static function sanitizeOriginalFilename(string $raw): string
    {
        $withoutControl = preg_replace('/[\p{Cc}\p{Cn}]/u', '', $raw) ?? '';
        $trimmed = trim($withoutControl);

        if ($trimmed === '') {
            throw new EditorialMediaInvariantViolation('Le nom d\'origine ne peut pas être vide.');
        }

        if (mb_strlen($trimmed) > self::ORIGINAL_FILENAME_MAX) {
            $trimmed = mb_substr($trimmed, 0, self::ORIGINAL_FILENAME_MAX);
        }

        return $trimmed;
    }

    private static function assertPositive(string $label, int $value): void
    {
        if ($value <= 0) {
            throw new EditorialMediaInvariantViolation(\sprintf(
                'La %s doit être strictement positive (reçu %d).',
                $label,
                $value,
            ));
        }
    }
}
