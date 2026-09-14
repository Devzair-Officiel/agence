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

    /**
     * Retourne tous les UUID connus au moment de l'appel, triés par
     * `createdAt DESC, id DESC`. Ce snapshot est lu en une seule requête
     * pour éviter le bug de pagination OFFSET lors d'une suppression simultanée.
     *
     * @return list<Uuid>
     */
    public function listAllIds(): array;

    /**
     * Vérifie rapidement (LIMIT 1) si le média est référencé par au moins
     * un article (tous statuts confondus : draft, published, archived).
     *
     * Utilisé par le handler de suppression pour refuser une suppression
     * qui provoquerait une image cassée.
     */
    public function isReferencedByAnyArticle(Uuid $id): bool;

    /**
     * Compte le nombre d'articles qui référencent chaque UUID de la liste.
     *
     * Renvoie un tableau indexé par rfc4122 string → nombre d'articles.
     * Les UUIDs sans référence ne sont pas présents dans le tableau
     * (valeur par défaut = 0 côté appelant).
     *
     * Utilisé pour enrichir le modèle de lecture de la liste admin sans N+1.
     *
     * @param list<Uuid> $ids
     *
     * @return array<string, int>
     */
    public function countUsagesBatch(array $ids): array;

    /**
     * Supprime le média de la base de données.
     *
     * Ne supprime PAS les fichiers physiques — c'est le handler applicatif
     * qui orchestre la suppression fichiers-puis-DB.
     */
    public function delete(MediaAsset $asset): void;
}
