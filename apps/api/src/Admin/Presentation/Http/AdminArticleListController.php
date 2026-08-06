<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Editorial\Application\Query\ListAdminArticles;
use App\Editorial\Application\Query\ListAdminArticlesHandler;
use App\Editorial\Domain\ArticleStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Liste paginée des articles pour l'administration éditoriale (Phase 8C3).
 *
 * Paramètres GET :
 *   - `page`   : entier ≥ 1 (clampé à 1 sinon) ;
 *   - `status` : `draft` | `published` | `archived` | vide (= tous).
 *
 * `per_page` figé à 20 en 8C3 (pas de paramètre) — évite qu'un lien exploré
 * ne renvoie un dump de milliers de lignes.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticleListController extends AbstractController
{
    private const PER_PAGE = 20;

    public function __construct(
        private readonly ListAdminArticlesHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', '1'));
        $status = $request->query->get('status');
        $statusFilter = \is_string($status) && $status !== '' ? ArticleStatus::tryFrom($status) : null;

        $result = $this->handler->__invoke(new ListAdminArticles($page, self::PER_PAGE, $statusFilter));

        return $this->render('admin/articles/list.html.twig', [
            'result' => $result,
            'status_options' => ArticleStatus::cases(),
            'current_status' => $statusFilter,
        ]);
    }
}
