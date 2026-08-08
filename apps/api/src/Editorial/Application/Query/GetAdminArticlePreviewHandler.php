<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Infrastructure\Markdown\CommonMarkArticleRenderer;

/**
 * Handler de prévisualisation admin (Phase 8C4).
 *
 * Contrat strict :
 *   1. charger l'article une seule fois par UUID via le port de lecture admin
 *      existant (`findForEdit`), pour ne pas dupliquer la stratégie de
 *      chargement Doctrine ;
 *   2. produire `contentHtml` via `CommonMarkArticleRenderer` — même moteur
 *      et même politique de sécurité (`MarkdownSecurityPolicy`) que le CLI
 *      d'import (Phase 8B1) ; garantit qu'un article prévisualisé rend
 *      exactement comme il rendrait côté site public en cas de publication ;
 *   3. AUCUNE mutation, AUCUNE persistance, AUCUNE mise à jour d'horodatage ;
 *   4. n'expose ni `AdminUser`, ni session, ni mot de passe, ni PII :
 *      `AdminArticlePreviewView` ne contient que du contenu article.
 *
 * Défense en profondeur : si l'article n'existe pas, on lève
 * `ArticleNotFoundException` que le contrôleur traduit en 404 HTTP ; on ne
 * fabrique jamais une vue vide silencieuse.
 */
final class GetAdminArticlePreviewHandler
{
    public function __construct(
        private readonly AdminArticleReadRepositoryInterface $repository,
        private readonly CommonMarkArticleRenderer $renderer,
    ) {
    }

    public function __invoke(GetAdminArticlePreview $query): AdminArticlePreviewView
    {
        $edit = $this->repository->findForEdit($query->id);
        if ($edit === null) {
            throw ArticleNotFoundException::forId($query->id->toRfc4122());
        }

        return AdminArticlePreviewView::fromEditView(
            $edit,
            $this->renderer->renderHtml($edit->bodyMarkdown),
        );
    }
}
