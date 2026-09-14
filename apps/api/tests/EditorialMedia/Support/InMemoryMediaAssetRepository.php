<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Support;

use App\EditorialMedia\Domain\Exception\MediaAssetNotFoundException;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Adaptateur en mémoire pour les tests unitaires du handler. Reproduit
 * l'ordonnancement `createdAt DESC, id DESC` du repository Doctrine.
 */
final class InMemoryMediaAssetRepository implements MediaAssetRepositoryInterface
{
    /** @var array<string, MediaAsset> */
    private array $assets = [];

    /**
     * UUIDs rfc4122 considérés référencés par au moins un article.
     * Configuré par les tests via `markAsReferenced()`.
     *
     * @var array<string, int>
     */
    private array $usageCounts = [];

    public function save(MediaAsset $asset): void
    {
        $this->assets[$asset->id()->toRfc4122()] = $asset;
    }

    public function findById(Uuid $id): ?MediaAsset
    {
        return $this->assets[$id->toRfc4122()] ?? null;
    }

    public function getById(Uuid $id): MediaAsset
    {
        $asset = $this->findById($id);
        if ($asset === null) {
            throw new MediaAssetNotFoundException(\sprintf('Aucun média %s.', $id->toRfc4122()));
        }

        return $asset;
    }

    public function list(int $page, int $perPage): array
    {
        $ordered = $this->orderedByRecent();
        $offset = ($page - 1) * $perPage;

        return array_slice($ordered, $offset, $perPage);
    }

    public function count(): int
    {
        return \count($this->assets);
    }

    public function listAllIds(): array
    {
        return array_map(
            static fn (MediaAsset $a): Uuid => $a->id(),
            $this->orderedByRecent(),
        );
    }

    /** Helper de test : retire un asset de la collection (simule un flush Doctrine). */
    public function remove(Uuid $id): void
    {
        unset($this->assets[$id->toRfc4122()]);
    }

    /** Helper de test : configure le nombre d'articles référençant ce média. */
    public function markAsReferenced(Uuid $id, int $count = 1): void
    {
        $this->usageCounts[$id->toRfc4122()] = $count;
    }

    public function isReferencedByAnyArticle(Uuid $id): bool
    {
        return ($this->usageCounts[$id->toRfc4122()] ?? 0) > 0;
    }

    public function countUsagesBatch(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $rfc = $id->toRfc4122();
            if (isset($this->usageCounts[$rfc]) && $this->usageCounts[$rfc] > 0) {
                $result[$rfc] = $this->usageCounts[$rfc];
            }
        }

        return $result;
    }

    public function delete(MediaAsset $asset): void
    {
        unset($this->assets[$asset->id()->toRfc4122()]);
    }

    /**
     * @return list<MediaAsset>
     */
    private function orderedByRecent(): array
    {
        $items = array_values($this->assets);
        usort($items, static function (MediaAsset $a, MediaAsset $b): int {
            $cmp = $b->createdAt() <=> $a->createdAt();
            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp($b->id()->toRfc4122(), $a->id()->toRfc4122());
        });

        return $items;
    }
}
