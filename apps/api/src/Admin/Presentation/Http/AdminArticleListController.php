<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Editorial\Application\Query\ArticleSortField;
use App\Editorial\Application\Query\ListAdminArticles;
use App\Editorial\Application\Query\ListAdminArticlesHandler;
use App\Editorial\Domain\ArticleStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Liste paginée des articles pour l'administration éditoriale.
 *
 * Paramètres GET whitelistés :
 *   - `page`      : entier ≥ 1 (clampé à 1 sinon) ;
 *   - `status`    : `draft` | `published` | `archived` | vide (= tous) ;
 *   - `sort`      : `id` | `title` | `status` | `updated_at` (défaut : `updated_at`) ;
 *   - `direction` : `asc` | `desc` (défaut : `desc`).
 *
 * `sort` et `direction` sont mappés via enum — jamais passés bruts à Doctrine.
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

        $sortParam = $request->query->get('sort', 'updated_at');
        $sortField = \is_string($sortParam) ? ArticleSortField::tryFrom($sortParam) : null;
        $sortField ??= ArticleSortField::UpdatedAt;

        $directionParam = $request->query->get('direction', 'desc');
        $sortAscending = $directionParam === 'asc';

        $result = $this->handler->__invoke(new ListAdminArticles(
            $page,
            self::PER_PAGE,
            $statusFilter,
            $sortField,
            $sortAscending,
        ));

        return $this->render('admin/articles/list.html.twig', [
            'result'           => $result,
            'status_options'   => ArticleStatus::cases(),
            'current_status'   => $statusFilter,
            'current_sort'     => $sortField->value,
            'current_direction' => $sortAscending ? 'asc' : 'desc',
        ]);
    }
}
