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
