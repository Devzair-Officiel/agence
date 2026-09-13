<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Support;

use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Application\Storage\MediaVariantStorageInterface;
use App\EditorialMedia\Domain\StorageKey;
use App\EditorialMedia\Domain\VariantStorageKey;

/**
 * Stockage en mémoire pour les tests unitaires du handler.
 *
 * Implémente à la fois `MediaStorageInterface` (original) et
 * `MediaVariantStorageInterface` (variants) pour pouvoir être injecté
 * dans les deux ports du handler.
 *
 * `moveInto` / `moveVariantInto` copient le contenu du fichier temporaire
 * en mémoire et le suppriment du disque. Les tests inspectent `files` (map
 * clé → contenu) et les compteurs pour vérifier l'atomicité.
 */
final class FakeMediaStorage implements MediaStorageInterface, MediaVariantStorageInterface
{
    /** @var array<string, string> key → contents (original + variants) */
    public array $files = [];
    /** @var list<string> */
    public array $deletedKeys = [];
    public int $moveIntoCallCount        = 0;
    public int $moveVariantIntoCallCount = 0;
    public int $deleteCallCount          = 0;
    public int $deleteVariantCallCount   = 0;

    public function __construct(
        private readonly bool $moveFails        = false,
        private readonly bool $deleteFails      = false,
        private readonly bool $moveVariantFails = false,
    ) {
    }

    // ── MediaStorageInterface ─────────────────────────────────────────────

    public function moveInto(string $temporaryPath, StorageKey $destination): void
    {
        ++$this->moveIntoCallCount;

        if ($this->moveFails) {
            throw MediaStorageException::writeFailed();
        }

        $contents = @file_get_contents($temporaryPath);
        if ($contents === false) {
            throw MediaStorageException::readFailed();
        }

        $this->files[$destination->toString()] = $contents;
        @unlink($temporaryPath);
    }

    public function openReadStream(StorageKey $key)
    {
        if (!isset($this->files[$key->toString()])) {
            throw MediaStorageException::readFailed();
        }
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw MediaStorageException::readFailed();
        }
        fwrite($stream, $this->files[$key->toString()]);
        rewind($stream);

        return $stream;
    }

    public function exists(StorageKey $key): bool
    {
        return isset($this->files[$key->toString()]);
    }

    public function delete(StorageKey $key): void
    {
        ++$this->deleteCallCount;
        $this->deletedKeys[] = $key->toString();

        if ($this->deleteFails) {
            throw MediaStorageException::writeFailed();
        }

        unset($this->files[$key->toString()]);
    }

    // ── MediaVariantStorageInterface ──────────────────────────────────────

    public function moveVariantInto(string $temporaryPath, VariantStorageKey $destination): void
    {
        ++$this->moveVariantIntoCallCount;

        if ($this->moveVariantFails) {
            throw MediaStorageException::writeFailed();
        }

        $contents = @file_get_contents($temporaryPath);
        if ($contents === false) {
            throw MediaStorageException::readFailed();
        }

        $this->files[$destination->toString()] = $contents;
        @unlink($temporaryPath);
    }

    public function openVariantReadStream(VariantStorageKey $key)
    {
        if (!isset($this->files[$key->toString()])) {
            throw MediaStorageException::readFailed();
        }
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw MediaStorageException::readFailed();
        }
        fwrite($stream, $this->files[$key->toString()]);
        rewind($stream);

        return $stream;
    }

    public function variantExists(VariantStorageKey $key): bool
    {
        return isset($this->files[$key->toString()]);
    }

    public function deleteVariant(VariantStorageKey $key): void
    {
        ++$this->deleteVariantCallCount;
        $this->deletedKeys[] = $key->toString();

        if ($this->deleteFails) {
            throw MediaStorageException::writeFailed();
        }

        unset($this->files[$key->toString()]);
    }
}
