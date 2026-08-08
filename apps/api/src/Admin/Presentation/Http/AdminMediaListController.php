<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\EditorialMedia\Application\Query\ListAdminMedia;
use App\EditorialMedia\Application\Query\ListAdminMediaHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Liste paginée des médias éditoriaux (Phase 9A).
 *
 * `per_page` figé à 20 en 9A ; `page` clampée à ≥ 1 pour éviter qu'un lien
 * exploré ne provoque une requête négative ou hors bornes.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminMediaListController extends AbstractController
{
    private const PER_PAGE = 20;

    public function __construct(
        private readonly ListAdminMediaHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', '1'));

        $result = $this->handler->__invoke(new ListAdminMedia($page, self::PER_PAGE));

        return $this->render('admin/media/list.html.twig', [
            'result' => $result,
        ]);
    }
}
