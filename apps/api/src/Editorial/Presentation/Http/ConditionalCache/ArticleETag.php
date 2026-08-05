<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Http\ConditionalCache;

use App\Editorial\Application\View\ArticleDetailView;

/**
 * Calcul de la valeur brute utilisée comme ETag *faible* pour la vue
 * détail d'un article.
 *
 * Renvoie uniquement l'entité (le hash SHA-256), sans le préfixe `W/` ni
 * les guillemets : c'est `Response::setEtag($value, weak: true)` qui se
 * charge d'ajouter `W/"…"` conformément à RFC 7232. Retourner déjà
 * `W/"…"` ici conduirait à une double enveloppe (`W/"W/"…""`) car
 * Symfony wrap toute valeur ne commençant pas par `"`.
 *
 * Choix d'un ETag faible : le corps JSON contient `request_id` (UUID v7
 * régénéré à chaque requête), donc deux réponses ne sont jamais
 * byte-à-byte identiques. Un ETag fort mentirait sur cette propriété.
 * L'ETag faible signifie « sémantiquement équivalent » — parfait pour le
 * cache conditionnel où le client se moque du `request_id` mais veut
 * savoir si le contenu article a bougé.
 *
 * L'entrée du hash est :
 *     "{article_id}|{updated_at ATOM}|v1"
 *
 * - `article_id` change si l'article est ré-importé ;
 * - `updated_at` change à chaque `Article::publish()` ou `archive()` ;
 * - le suffixe `v1` permet d'invalider en masse le cache sans requérir un
 *   changement de contenu, si un jour le contrat JSON évolue.
 */
final class ArticleETag
{
    public const CONTRACT_VERSION = 'v1';

    public static function forDetail(ArticleDetailView $view): string
    {
        return self::hashOf(\sprintf(
            '%s|%s|%s',
            $view->id,
            $view->updatedAt,
            self::CONTRACT_VERSION,
        ));
    }

    public static function hashOf(string $material): string
    {
        return hash('sha256', $material);
    }
}
