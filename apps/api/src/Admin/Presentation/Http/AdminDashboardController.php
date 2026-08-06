<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Tableau de bord administrateur — Phase 8C1.
 *
 * Contenu volontairement minimal : identité connectée, dernière connexion,
 * lien logout. Les fonctionnalités éditoriales (édition/publication) sont
 * introduites en Phase 8C2 et suivantes.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminDashboardController extends AbstractController
{
    public function __invoke(): Response
    {
        /** @var AdminUser $user */
        $user = $this->getUser();

        return $this->render('admin/dashboard.html.twig', [
            'admin_user' => $user,
        ]);
    }
}
