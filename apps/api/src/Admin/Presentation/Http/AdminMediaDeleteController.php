<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\EditorialMedia\Application\Command\DeleteEditorialMedia;
use App\EditorialMedia\Application\Command\DeleteEditorialMediaHandler;
use App\EditorialMedia\Application\Exception\MediaInUseException;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Domain\Exception\MediaAssetNotFoundException;
use App\EditorialMedia\Infrastructure\Logging\MediaAdminAuditLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Suppression d'un média éditorial depuis l'administration (Phase 9D).
 *
 * POST uniquement — le formulaire de confirmation dans la liste envoie
 * `_csrf_token` + `_method=DELETE` (convention HTML Symfony).
 *
 * Sécurité :
 *   - `#[IsGranted('ROLE_ADMIN')]` + firewall `admin` ;
 *   - CSRF token `media_delete_{uuid}` (spécifique à chaque ressource) ;
 *   - rate limit `admin_write` consommé APRÈS la vérification CSRF, AVANT
 *     toute mutation — une tentative refusée ne compte pas.
 *
 * Comportement :
 *   - 404 si le média est introuvable (le CSRF est quand même vérifié avant).
 *   - 409 si le média est référencé par un article : flash `error` +
 *     redirection vers la liste (pas de page dédiée pour ne pas complexifier).
 *   - 429 si le rate limiter refuse.
 *   - 200 (via redirect 303) sur succès, flash `success`.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminMediaDeleteController extends AbstractController
{
    public function __construct(
        private readonly DeleteEditorialMediaHandler $handler,
        private readonly AdminActionRateLimiter $rateLimiter,
        private readonly MediaAdminAuditLogger $audit,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $uuid = $this->parseUuidOrNotFound($id);

        if (!$this->isCsrfTokenValid('media_delete_'.$uuid->toRfc4122(), (string) $request->request->get('_csrf_token'))) {
            $this->audit->deleteFailed($admin, 'csrf_invalid', $uuid->toRfc4122());
            $this->addFlash('error', 'Jeton de sécurité expiré. Réessayez.');

            return new RedirectResponse(
                $this->generateUrl('admin_media_list'),
                Response::HTTP_SEE_OTHER,
            );
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->deleteFailed($admin, 'rate_limited', $uuid->toRfc4122());

            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label'        => 'la suppression d\'un média',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        try {
            $mime = $this->handler->__invoke(new DeleteEditorialMedia($uuid));
        } catch (MediaAssetNotFoundException) {
            $this->audit->deleteFailed($admin, 'not_found', $uuid->toRfc4122());
            throw new NotFoundHttpException();
        } catch (MediaInUseException) {
            $this->audit->deleteFailed($admin, 'media_in_use', $uuid->toRfc4122());
            $this->addFlash('error', 'Ce média est utilisé par au moins un article et ne peut pas être supprimé.');

            return new RedirectResponse(
                $this->generateUrl('admin_media_list'),
                Response::HTTP_SEE_OTHER,
            );
        } catch (MediaStorageException) {
            $this->audit->deleteFailed($admin, 'storage_failure', $uuid->toRfc4122());
            $this->addFlash('error', 'La suppression des fichiers a échoué. Réessayez ou contactez l\'exploitation.');

            return new RedirectResponse(
                $this->generateUrl('admin_media_list'),
                Response::HTTP_SEE_OTHER,
            );
        }

        $this->audit->deleted($admin, $uuid->toRfc4122(), $mime);
        $this->addFlash('success', 'Média supprimé.');

        return new RedirectResponse(
            $this->generateUrl('admin_media_list'),
            Response::HTTP_SEE_OTHER,
        );
    }

    private function parseUuidOrNotFound(string $id): Uuid
    {
        try {
            return Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException();
        }
    }
}
