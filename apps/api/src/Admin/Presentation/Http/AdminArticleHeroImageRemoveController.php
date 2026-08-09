<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\EditorialAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Editorial\Application\Command\RemoveDraftArticleHeroImage;
use App\Editorial\Application\Command\RemoveDraftArticleHeroImageHandler;
use App\Editorial\Domain\Exception\ArticleNotEditableException;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * POST-only : retire l'image principale d'un brouillon (Phase 9B).
 *
 * Le formulaire déclencheur vit dans `edit.html.twig` — il ne prend qu'un
 * jeton CSRF et n'ouvre pas de nouvel écran. En cas d'échec (statut non-Draft,
 * rate limit, CSRF invalide), on revient à l'édition avec un flash et un
 * statut HTTP significatif.
 *
 * Aucune suppression physique du `MediaAsset` — voir docblock du handler.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticleHeroImageRemoveController extends AbstractController
{
    public function __construct(
        private readonly RemoveDraftArticleHeroImageHandler $removeHandler,
        private readonly AdminActionRateLimiter $rateLimiter,
        private readonly EditorialAdminAuditLogger $audit,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $uuid = $this->parseUuidOrNotFound($id);
        $rfc = $uuid->toRfc4122();

        if (!$this->isCsrfTokenValid('article_hero_image_remove_'.$rfc, (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'hero_image_remove', 'csrf_invalid', $rfc);
            $this->addFlash('error', 'Le jeton de sécurité a expiré. Merci de renvoyer le formulaire.');

            return $this->redirectToEdit($rfc, Response::HTTP_SEE_OTHER);
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'hero_image_remove', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label' => 'le retrait de l\'image principale',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        try {
            $result = $this->removeHandler->__invoke(new RemoveDraftArticleHeroImage($uuid));
        } catch (ArticleNotFoundException) {
            throw new NotFoundHttpException();
        } catch (ArticleNotEditableException $e) {
            $this->audit->actionFailed($admin, 'hero_image_remove', 'not_editable', $rfc);
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToEdit($rfc, Response::HTTP_SEE_OTHER);
        }

        if ($result->mutated) {
            $this->audit->heroImageRemoved($admin, $rfc);
            $this->addFlash('success', 'Image principale retirée du brouillon.');
        } else {
            $this->audit->heroImageNoop($admin, $rfc, 'remove');
            $this->addFlash('info', 'Ce brouillon n\'avait pas d\'image principale à retirer.');
        }

        return $this->redirectToEdit($rfc, Response::HTTP_SEE_OTHER);
    }

    private function redirectToEdit(string $id, int $status): RedirectResponse
    {
        return new RedirectResponse(
            $this->generateUrl('admin_article_edit', ['id' => $id]),
            $status,
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
