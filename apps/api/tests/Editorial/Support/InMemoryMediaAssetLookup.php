<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Support;

use App\Editorial\Application\Media\MediaAssetDescriptor;
use App\Editorial\Application\Media\MediaAssetLookupInterface;
use App\Editorial\Application\Media\MediaAssetReaderInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Fake in-memory des deux ports `MediaAssetLookupInterface` et
 * `MediaAssetReaderInterface` — sert exclusivement aux tests
 * Application du contexte `Editorial`.
 *
 * Un seul fake pour les deux ports (l'adaptateur réel `EditorialMediaAssetLookup`
 * fait la même chose), ce qui garantit qu'un test ne peut pas voir un média
 * existant côté lookup sans qu'un descriptor correspondant existe côté reader
 * — ou vice-versa. C'est exactement l'invariant applicatif que la production
 * respecte.
 */
final class InMemoryMediaAssetLookup implements MediaAssetLookupInterface, MediaAssetReaderInterface
{
    /**
     * @var array<string, MediaAssetDescriptor>
     */
    private array $descriptors = [];

    public function register(MediaAssetDescriptor $descriptor): void
    {
        $this->descriptors[$descriptor->id->toRfc4122()] = $descriptor;
    }

    public function exists(Uuid $mediaAssetId): bool
    {
        return isset($this->descriptors[$mediaAssetId->toRfc4122()]);
    }

    public function findMetadata(Uuid $mediaAssetId): ?MediaAssetDescriptor
    {
        return $this->descriptors[$mediaAssetId->toRfc4122()] ?? null;
    }
}
