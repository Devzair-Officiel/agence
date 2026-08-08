<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Storage;

use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Domain\StorageKey;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Stockage local des médias éditoriaux dans un répertoire hors web.
 *
 * Sécurité :
 *   - Le répertoire racine est résolu une fois à la construction et servira
 *     de préfixe autoritaire. Toute clé qui, après concaténation, sort de ce
 *     préfixe est refusée avec `MediaStorageException` (défense en profondeur
 *     contre un `StorageKey` compromis — la classe VO garantit déjà le format).
 *   - `moveInto` crée le sous-répertoire UUID avec `chmod 0750` et écrit le
 *     fichier avec `chmod 0640`.
 *   - `delete` supprime le fichier ET le dossier UUID parent s'il est vide,
 *     pour éviter les répertoires orphelins après compensation.
 *
 * Ne loggue rien ici : c'est la couche audit (contrôleur) qui décide de quoi
 * consigner et à quel niveau.
 */
final class LocalMediaStorage implements MediaStorageInterface
{
    private readonly string $normalizedRoot;

    public function __construct(
        string $storageRoot,
        private readonly Filesystem $filesystem,
    ) {
        $this->filesystem->mkdir($storageRoot, 0750);

        $real = realpath($storageRoot);
        if ($real === false) {
            throw MediaStorageException::writeFailed();
        }
        $this->normalizedRoot = rtrim($real, \DIRECTORY_SEPARATOR);
    }

    public function moveInto(string $temporaryPath, StorageKey $destination): void
    {
        $absolute = $this->resolveInsideRoot($destination);
        $parent = \dirname($absolute);

        try {
            $this->filesystem->mkdir($parent, 0750);
        } catch (\Throwable) {
            throw MediaStorageException::writeFailed();
        }

        if (!@rename($temporaryPath, $absolute)) {
            throw MediaStorageException::writeFailed();
        }
        @chmod($absolute, 0640);
    }

    public function openReadStream(StorageKey $key)
    {
        $absolute = $this->resolveInsideRoot($key);

        if (!is_file($absolute)) {
            throw MediaStorageException::readFailed();
        }

        $handle = @fopen($absolute, 'rb');
        if ($handle === false) {
            throw MediaStorageException::readFailed();
        }

        return $handle;
    }

    public function exists(StorageKey $key): bool
    {
        try {
            $absolute = $this->resolveInsideRoot($key);
        } catch (MediaStorageException) {
            return false;
        }

        return is_file($absolute);
    }

    public function delete(StorageKey $key): void
    {
        $absolute = $this->resolveInsideRoot($key);

        if (is_file($absolute) && !@unlink($absolute)) {
            throw MediaStorageException::writeFailed();
        }

        $parent = \dirname($absolute);
        if (
            $parent !== $this->normalizedRoot
            && is_dir($parent)
            && self::isDirectoryEmpty($parent)
        ) {
            @rmdir($parent);
        }
    }

    /**
     * Défense en profondeur : la clé de stockage est déjà normalisée par le
     * VO `StorageKey` (regex `{uuid}/original.{ext}`), qui exclut toute
     * séquence `..`, `/`, backslash ou nul. On refuse malgré tout tout chemin
     * dont la concaténation ne resterait pas préfixée par la racine.
     */
    private function resolveInsideRoot(StorageKey $key): string
    {
        $raw = $key->toString();
        if (str_contains($raw, '..') || str_contains($raw, "\0")) {
            throw MediaStorageException::writeFailed();
        }

        $absolute = $this->normalizedRoot . \DIRECTORY_SEPARATOR . $raw;
        if (!str_starts_with($absolute, $this->normalizedRoot . \DIRECTORY_SEPARATOR)) {
            throw MediaStorageException::writeFailed();
        }

        return $absolute;
    }

    private static function isDirectoryEmpty(string $path): bool
    {
        $handle = @opendir($path);
        if ($handle === false) {
            return false;
        }
        try {
            while (($entry = readdir($handle)) !== false) {
                if ($entry !== '.' && $entry !== '..') {
                    return false;
                }
            }
        } finally {
            closedir($handle);
        }

        return true;
    }
}
