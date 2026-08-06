<?php

declare(strict_types=1);

namespace App\Editorial\Application\Command;

use Symfony\Component\Uid\Uuid;

/**
 * Publication d'un brouillon depuis l'administration.
 *
 * Distinct de `PublishArticleBySlug` :
 *   - adressage par UUID (identifiant stable, indépendant du slug) ;
 *   - `publishedAt` implicite = « maintenant » (l'admin n'anticipe pas de
 *     publication future en Phase 8C3 — la fenêtre planifiée est réservée
 *     à la CLI de publication) ;
 *   - exige strictement le statut `Draft` (pas d'idempotence sur un article
 *     déjà publié, pas de bascule depuis `Archived`).
 */
final class PublishDraftArticle
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }
}
