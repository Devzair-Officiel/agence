<?php

declare(strict_types=1);

namespace App\EditorialMedia\Infrastructure\Persistence;

use App\EditorialMedia\Domain\Exception\MediaAssetNotFoundException;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Implémentation Doctrine du port `MediaAssetRepositoryInterface`.
 *
 * Ne fait aucun `flush` automatique — c'est le handler applicatif qui décide
 * du moment, pour permettre la compensation (suppression du fichier stocké)
 * en cas d'échec de flush.
 */
final class DoctrineMediaAssetRepository implements MediaAssetRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(MediaAsset $asset): void
    {
        $this->entityManager->persist($asset);
    }

    public function findById(Uuid $id): ?MediaAsset
    {
        $asset = $this->entityManager
            ->getRepository(MediaAsset::class)
            ->find($id);

        return $asset instanceof MediaAsset ? $asset : null;
    }

    public function getById(Uuid $id): MediaAsset
    {
        $asset = $this->findById($id);

        if ($asset === null) {
            throw new MediaAssetNotFoundException(\sprintf(
                'Aucun média avec l\'identifiant %s.',
                $id->toRfc4122(),
            ));
        }

        return $asset;
    }

    public function list(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;

        $qb = $this->entityManager->createQueryBuilder()
            ->select('m')
            ->from(MediaAsset::class, 'm')
            ->orderBy('m.createdAt', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        /** @var list<MediaAsset> $result */
        $result = array_values($qb->getQuery()->getResult());

        return $result;
    }

    public function count(): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(m.id)')
            ->from(MediaAsset::class, 'm');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
