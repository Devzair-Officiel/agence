<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain;

use App\EditorialMedia\Domain\Exception\MediaAssetNotFoundException;
use Symfony\Component\Uid\Uuid;

/**
 * Port du domaine EditorialMedia.
 *
 * L'implémentation par défaut est Doctrine (`DoctrineMediaAssetRepository`).
 * Les tests de la couche Application peuvent injecter une variante en mémoire
 * pour rester rapides et déterministes.
 *
 * En Phase 9A, l'admin est le seul lecteur : aucune méthode publique ne filtre
 * par statut de publication (les médias ne sont pas encore rattachés à un
 * article). Les listes sont ordonnées par `createdAt DESC, id DESC` pour offrir
 * un flux stable aux administrateurs.
 */
interface MediaAssetRepositoryInterface
{
    public function save(MediaAsset $asset): void;

    /**
     * Résout un média par son identifiant. Renvoie `null` si aucun média ne
     * porte cet UUID — le contrôleur d'admin traduit systématiquement en 404
     * (aucune divulgation d'existence).
     */
    public function findById(Uuid $id): ?MediaAsset;

    /**
     * Résout un média par son identifiant ou lève `MediaAssetNotFoundException`.
     *
     * @throws MediaAssetNotFoundException
     */
    public function getById(Uuid $id): MediaAsset;

    /**
     * Page ordonnée par `createdAt DESC, id DESC`.
     *
     * @param int<1, max> $page
     * @param int<1, max> $perPage
     *
     * @return list<MediaAsset>
     */
    public function list(int $page, int $perPage): array;

    public function count(): int;
}
