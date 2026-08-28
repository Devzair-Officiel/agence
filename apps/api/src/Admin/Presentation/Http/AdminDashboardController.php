<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Application\Query\GetAdminDashboardSummaryHandler;
use App\Admin\Domain\AdminUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Tableau de bord administrateur (Phase R2).
 *
 * Délègue l'agrégation des données éditoriales au handler dédié afin que le
 * contrôleur reste une simple orchestration HTTP → Twig.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminDashboardController extends AbstractController
{
    public function __construct(
        private readonly GetAdminDashboardSummaryHandler $summaryHandler,
    ) {
    }

    public function __invoke(): Response
    {
        /** @var AdminUser $user */
        $user = $this->getUser();

        return $this->render('admin/dashboard.html.twig', [
            'admin_user' => $user,
            'summary' => ($this->summaryHandler)(),
        ]);
    }
}
