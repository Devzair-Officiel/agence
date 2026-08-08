<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Support;

use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Domain\StorageKey;

/**
 * Stockage en mémoire pour les tests unitaires du handler.
 *
 * `moveInto` copie le contenu du fichier temporaire en mémoire et le supprime
 * du disque (mimique un déplacement atomique). Les tests peuvent inspecter les
 * clés déplacées et vérifier la compensation (`delete`) en cas d'échec.
 */
final class FakeMediaStorage implements MediaStorageInterface
{
    /** @var array<string, string> */
    public array $files = [];
    /** @var list<string> */
    public array $deletedKeys = [];
    public int $moveIntoCallCount = 0;
    public int $deleteCallCount = 0;

    public function __construct(
        private readonly bool $moveFails = false,
        private readonly bool $deleteFails = false,
    ) {
    }

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
}
