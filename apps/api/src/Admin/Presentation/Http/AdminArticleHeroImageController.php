<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\EditorialAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Editorial\Application\Command\SetDraftArticleHeroImage;
use App\Editorial\Application\Command\SetDraftArticleHeroImageHandler;
use App\Editorial\Application\Media\HeroMediaNotFoundException;
use App\Editorial\Application\Query\GetAdminArticleForEdit;
use App\Editorial\Application\Query\GetAdminArticleForEditHandler;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotEditableException;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\EditorialMedia\Application\Query\ListAdminMedia;
use App\EditorialMedia\Application\Query\ListAdminMediaHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Chooser SSR d'image principale pour un brouillon d'article (Phase 9B).
 *
 * GET  → affiche la bibliothèque paginée en mode radio + champ alt.
 * POST → applique `SetDraftArticleHeroImage` puis PRG vers l'édition.
 *
 * L'écran refuse d'être invoqué sur un article Published/Archived : le
 * chooser lui-même est verrouillé (`assertDraftEditable` est porté par
 * l'agrégat côté handler, mais l'affichage évite de proposer une action
 * qui échouerait — voir §7 du brief). En pratique le contrôleur GET rend
 * un état « lecture seule » avec statut 409.
 *
 * Aucun JavaScript requis : le chooser est un `<form method="post">`
 * avec `<input type="radio">` par miniature et un `<input type="text">`
 * pour l'alt. La pagination reste `?page=N` en GET.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticleHeroImageController extends AbstractController
{
    private const PER_PAGE = 20;

    public function __construct(
        private readonly GetAdminArticleForEditHandler $viewHandler,
        private readonly ListAdminMediaHandler $listHandler,
        private readonly SetDraftArticleHeroImageHandler $setHandler,
        private readonly AdminActionRateLimiter $rateLimiter,
        private readonly EditorialAdminAuditLogger $audit,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $uuid = $this->parseUuidOrNotFound($id);
        try {
            $view = $this->viewHandler->__invoke(new GetAdminArticleForEdit($uuid));
        } catch (ArticleNotFoundException) {
            throw new NotFoundHttpException();
        }

        $page = max(1, (int) $request->query->get('page', '1'));
        $mediaPage = $this->listHandler->__invoke(new ListAdminMedia($page, self::PER_PAGE));

        // GET : rendu du chooser (ou de la vue « verrouillé » si non Draft).
        if ($request->isMethod('GET')) {
            $selectedMediaId = $view->heroImage?->mediaId;
            $altValue = $view->heroImage?->altText ?? '';

            $status = $view->status->value === 'draft' ? Response::HTTP_OK : Response::HTTP_CONFLICT;

            return $this->render('admin/articles/hero_image.html.twig', [
                'view' => $view,
                'media_page' => $mediaPage,
                'selected_media_id' => $selectedMediaId,
                'alt_value' => $altValue,
                'error_global' => null,
                'error_media' => null,
                'error_alt' => null,
            ], new Response('', $status));
        }

        // POST : mutation via SetDraftArticleHeroImage.
        if (!$this->isCsrfTokenValid('article_hero_image_set_'.$view->id, (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'hero_image_set', 'csrf_invalid', $view->id);

            return $this->renderWithError(
                $view,
                $mediaPage,
                selectedMediaId: (string) $request->request->get('media_id', ''),
                altValue: (string) $request->request->get('alt_text', ''),
                errorGlobal: 'Le jeton de sécurité a expiré. Merci de renvoyer le formulaire.',
                status: Response::HTTP_FORBIDDEN,
            );
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'hero_image_set', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label' => 'la modification de l\'image principale',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        $rawMediaId = trim((string) $request->request->get('media_id', ''));
        $rawAlt = (string) $request->request->get('alt_text', '');

        if ($rawMediaId === '') {
            $this->audit->actionFailed($admin, 'hero_image_set', 'media_missing', $view->id);

            return $this->renderWithError(
                $view,
                $mediaPage,
                selectedMediaId: null,
                altValue: $rawAlt,
                errorMedia: 'Sélectionnez une image dans la bibliothèque.',
                status: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        try {
            $mediaUuid = Uuid::fromString($rawMediaId);
        } catch (\InvalidArgumentException) {
            $this->audit->actionFailed($admin, 'hero_image_set', 'media_id_invalid', $view->id);

            return $this->renderWithError(
                $view,
                $mediaPage,
                selectedMediaId: null,
                altValue: $rawAlt,
                errorMedia: 'L\'identifiant du média sélectionné est invalide.',
                status: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        try {
            $result = $this->setHandler->__invoke(new SetDraftArticleHeroImage(
                articleId: $uuid,
                mediaAssetId: $mediaUuid,
                altText: $rawAlt,
            ));
        } catch (ArticleNotEditableException $e) {
            $this->audit->actionFailed($admin, 'hero_image_set', 'not_editable', $view->id);

            return $this->renderWithError(
                $view,
                $mediaPage,
                selectedMediaId: $rawMediaId,
                altValue: $rawAlt,
                errorGlobal: $e->getMessage(),
                status: Response::HTTP_CONFLICT,
            );
        } catch (HeroMediaNotFoundException $e) {
            $this->audit->actionFailed($admin, 'hero_image_set', 'media_not_found', $view->id);

            return $this->renderWithError(
                $view,
                $mediaPage,
                selectedMediaId: null,
                altValue: $rawAlt,
                errorMedia: $e->getMessage(),
                status: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        } catch (ArticleInvariantViolation $e) {
            $this->audit->actionFailed($admin, 'hero_image_set', 'invariant_violation', $view->id);

            return $this->renderWithError(
                $view,
                $mediaPage,
                selectedMediaId: $rawMediaId,
                altValue: $rawAlt,
                errorAlt: $e->getMessage(),
                status: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        if ($result->mutated) {
            $this->audit->heroImageSet($admin, $view->id, $mediaUuid->toRfc4122());
            $this->addFlash('success', 'Image principale enregistrée.');
        } else {
            $this->audit->heroImageNoop($admin, $view->id, 'set');
            $this->addFlash('info', 'Aucune modification détectée sur l\'image principale.');
        }

        return new RedirectResponse(
            $this->generateUrl('admin_article_edit', ['id' => $view->id]),
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

    private function renderWithError(
        \App\Editorial\Application\Query\AdminArticleEditView $view,
        \App\EditorialMedia\Application\Query\AdminMediaListPage $mediaPage,
        ?string $selectedMediaId,
        string $altValue,
        int $status,
        ?string $errorGlobal = null,
        ?string $errorMedia = null,
        ?string $errorAlt = null,
    ): Response {
        return $this->render('admin/articles/hero_image.html.twig', [
            'view' => $view,
            'media_page' => $mediaPage,
            'selected_media_id' => $selectedMediaId,
            'alt_value' => $altValue,
            'error_global' => $errorGlobal,
            'error_media' => $errorMedia,
            'error_alt' => $errorAlt,
        ], new Response('', $status));
    }
}
