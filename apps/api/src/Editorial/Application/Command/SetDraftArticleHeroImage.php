<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use Symfony\Component\Uid\Uuid;

/**
 * Rattache (ou remplace) l'image principale d'un brouillon.
 *
 * Command distincte de `UpdateDraftArticle` : le rattachement de média est
 * une intention éditoriale spécifique qui mérite son propre contrat plutôt
 * que d'être noyé dans un patch générique. Cela :
 *   - documente explicitement le workflow admin (URL, form, PRG dédiés) ;
 *   - simplifie le rate limiting et le logging d'audit (`hero_image_set`
 *     vs mise à jour de champs) ;
 *   - évite d'étendre `UpdateDraftArticle` avec 2 champs supplémentaires
 *     dont la validation cross-context (existence du média) tomberait sur
 *     un handler déjà fourni en dépendances.
 *
 * Le VO `ArticleHeroImage` est reconstruit CÔTÉ HANDLER — la command reste
 * un DTO plat au format « données HTTP » (chaînes trimées côté contrôleur).
 *
 * `altText` n'est jamais dérivé automatiquement du filename, du slug ou
 * du titre : il est saisi éditorialement par le rédacteur.
 */
final class SetDraftArticleHeroImage
{
    public function __construct(
        public readonly Uuid $articleId,
        public readonly Uuid $mediaAssetId,
        public readonly string $altText,
    ) {
    }
}
