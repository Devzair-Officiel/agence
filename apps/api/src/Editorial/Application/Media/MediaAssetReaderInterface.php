<?php

declare(strict_types=1);

namespace App\Editorial\Application\Media;

use Symfony\Component\Uid\Uuid;

/**
 * Port applicatif de LECTURE pour les métadonnées d'un `MediaAsset`.
 *
 * Distinct de `MediaAssetLookupInterface` :
 *   - `MediaAssetLookupInterface::exists()` sert aux HANDLERS de mutation
 *     (validation avant écriture) — question binaire, aucun DTO à charger ;
 *   - `MediaAssetReaderInterface::findMetadata()` sert aux VIEW HANDLERS
 *     (composition d'un DTO plat admin/public) — charge les métadonnées
 *     nécessaires au rendu, jamais les internals filesystem.
 *
 * Séparer les deux ports garde l'agrégat `Article` isolé du modèle
 * `MediaAsset` : un lookup binaire ne peut pas être détourné pour charger
 * transitivement un objet Doctrine.
 *
 * L'adaptateur canonique est `EditorialMediaAssetLookup` (implémente les
 * deux interfaces), qui traduit ces questions en requêtes Doctrine bornées.
 */
interface MediaAssetReaderInterface
{
    /**
     * Renvoie un `MediaAssetDescriptor` si le média existe, ou `null`.
     * Aucun filtre de statut publication n'est appliqué — le DTO est
     * autant utilisé côté admin (média orphelin acceptable) que public
     * (la sémantique publication est portée par l'appelant : cf.
     * `PublicMediaReadRepositoryInterface` en Checkpoint 2).
     */
    public function findMetadata(Uuid $mediaAssetId): ?MediaAssetDescriptor;
}
