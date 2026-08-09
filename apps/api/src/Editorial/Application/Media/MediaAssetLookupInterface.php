<?php

declare(strict_types=1);

namespace App\Editorial\Application\Media;

use Symfony\Component\Uid\Uuid;

/**
 * Port applicatif permettant au workflow `Article` de vérifier l'existence
 * d'un média sans dépendre des internals Doctrine du contexte borné
 * `EditorialMedia`.
 *
 * L'interface se limite à une question binaire — c'est volontaire :
 *   - le domaine `Editorial` n'a jamais besoin de connaître le stockage ni
 *     les métadonnées d'un média pour valider un rattachement ;
 *   - une opération plus riche (chargement d'entité, métadonnées) rendrait
 *     l'agrégat `Article` transitivement dépendant d'`EditorialMedia`, ce que
 *     l'invariant de séparation des contextes proscrit.
 *
 * L'adaptateur canonique est `EditorialMediaAssetLookup`
 * (`src/EditorialMedia/Infrastructure/Adapter/`) : il traduit la question en
 * requête Doctrine bornée (`MediaAssetRepositoryInterface::findById`).
 * Les tests unitaires application/domaine passent par un fake in-memory
 * (voir `tests/Editorial/Support/InMemoryMediaAssetLookup`).
 */
interface MediaAssetLookupInterface
{
    /**
     * Renvoie vrai si un `MediaAsset` porte cet identifiant, faux sinon.
     *
     * Le contrat ne dit rien sur le statut publication du média — c'est à
     * l'appelant de porter la sémantique (par exemple : lookup avant
     * rattachement à un `Article`).
     */
    public function exists(Uuid $mediaAssetId): bool;
}
