<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Affiche le formulaire de login administrateur.
 *
 * Le POST est intercepté par le firewall (form_login) — le contrôleur ne
 * traite jamais lui-même la vérification du mot de passe. Il ne gère que
 * l'affichage du formulaire et l'exposition du dernier identifiant tapé
 * (via `AuthenticationUtils`, sans PII persistante).
 *
 * Si un administrateur déjà connecté visite `/admin/login`, on redirige
 * directement vers le dashboard — évite un flash désorientant.
 */
final class AdminLoginController extends AbstractController
{
    public function __invoke(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($this->generateUrl('admin_dashboard'));
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastEmail = $authenticationUtils->getLastUsername();

        return $this->render('admin/login.html.twig', [
            'error' => $error,
            'last_email' => $lastEmail,
        ]);
    }
}
