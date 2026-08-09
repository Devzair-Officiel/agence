<?php

declare(strict_types=1);

namespace App\Editorial\Application\Media;

use Symfony\Component\Uid\Uuid;

/**
 * Levée quand un handler de rattachement d'image principale reçoit un
 * `mediaAssetId` qui n'existe pas dans le contexte `EditorialMedia`.
 *
 * Distincte de `ArticleNotFoundException` (article introuvable) et de
 * `ArticleInvariantViolation` (violation d'invariant intrinsèque au
 * contenu) : cette exception marque une erreur de RÉFÉRENCE entre deux
 * contextes bornés. Elle est traduite par la couche présentation admin
 * en 422 avec un message éditorial du type « Le média sélectionné n'existe
 * plus ; rafraîchissez la bibliothèque. »
 *
 * L'agrégat `Article` reste strictement inchangé après le throw : le
 * handler valide l'existence AVANT d'appeler `changeHeroImage()`.
 */
final class HeroMediaNotFoundException extends \DomainException
{
    public static function forId(Uuid $mediaAssetId): self
    {
        return new self(\sprintf(
            'Aucun média éditorial ne porte l\'identifiant %s.',
            $mediaAssetId->toRfc4122(),
        ));
    }
}
