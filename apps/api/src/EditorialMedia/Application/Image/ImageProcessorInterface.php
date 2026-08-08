<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Image;

use App\EditorialMedia\Application\Exception\ImageDimensionsExceededException;
use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Domain\MediaType;

/**
 * Port sortant : la couche Application demande à Infrastructure de :
 *   1. décoder l'image reçue (contrôle réel du format, pas seulement du MIME) ;
 *   2. la réencoder dans un fichier temporaire → toutes les métadonnées EXIF /
 *      GPS / commentaires disparaissent mécaniquement ;
 *   3. renvoyer les caractéristiques observées (dimensions, MIME final, hash).
 *
 * L'implémentation par défaut est `GdImageProcessor` (GD natif) ; toute autre
 * bibliothèque compatible pourrait remplacer cette implémentation sans changer
 * la couche Application.
 *
 * Contrat de robustesse :
 *   - N'accepte pas de contenu binaire en mémoire — l'appelant fournit un
 *     chemin sur disque (limite la surface d'attaque et permet le streaming
 *     éventuel côté implémentation).
 *   - Le MIME `$declaredType` correspond au MIME détecté serveur AVANT décodage
 *     (via `finfo`). Le processeur doit ré-vérifier que le décodage produit
 *     bien un format compatible ; en cas de contrefaçon, lève
 *     `InvalidImageException`.
 *   - Le fichier temporaire renvoyé n'est PAS auto-nettoyé : c'est au handler
 *     de le déplacer ou de le supprimer explicitement, y compris en cas
 *     d'échec ultérieur.
 */
interface ImageProcessorInterface
{
    /**
     * @param non-empty-string $sourcePath chemin absolu du fichier téléversé
     *
     * @throws InvalidImageException              image non décodable ou MIME contrefait
     * @throws ImageDimensionsExceededException   dimensions au-delà des bornes de la policy
     */
    public function normalize(string $sourcePath, MediaType $declaredType): NormalizedImage;
}
