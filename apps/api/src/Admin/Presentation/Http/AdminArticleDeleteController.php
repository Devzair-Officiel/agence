<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\EditorialAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Editorial\Application\Command\DeleteArchivedArticle;
use App\Editorial\Application\Command\DeleteArchivedArticleHandler;
use App\Editorial\Application\Query\GetAdminArticleForEdit;
use App\Editorial\Application\Query\GetAdminArticleForEditHandler;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\Exception\ArticleNotDeletableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Suppression définitive d'un article archivé.
 *
 * GET  /admin/articles/{id}/delete → page de confirmation (form avec CSRF).
 * POST /admin/articles/{id}/delete → suppression effective.
 *
 * Sécurité :
 *   - `#[IsGranted('ROLE_ADMIN')]` + firewall `admin`.
 *   - CSRF token `article_delete_{uuid}` (spécifique à chaque article).
 *   - Rate limit `admin_write` consommé APRÈS la vérification CSRF.
 *   - La règle métier (status === Archived) est garantie par le handler,
 *     indépendamment de ce que l'UI affiche.
 *   - Un UUID invalide ou inconnu renvoie 404 sans révéler l'existence.
 *
 * Médias : seul l'enregistrement `editorial_article` est supprimé. Le
 * fichier physique et l'entité `editorial_media_asset` sont conservés
 * (pas de propriété exclusive garantie par l'architecture inter-contexte).
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticleDeleteController extends AbstractController
{
    public function __construct(
        private readonly GetAdminArticleForEditHandler $viewHandler,
        private readonly DeleteArchivedArticleHandler $handler,
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

        if ($view->status !== ArticleStatus::Archived) {
            $this->addFlash('error', 'L\'article doit être archivé avant de pouvoir être supprimé définitivement.');

            return $this->redirectToEdit($uuid);
        }

        if ($request->isMethod('GET')) {
            return $this->render('admin/articles/delete_confirm.html.twig', [
                'view' => $view,
            ]);
        }

        // POST — vérification CSRF avant toute mutation.
        if (!$this->isCsrfTokenValid('article_delete_'.$uuid->toRfc4122(), (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'delete', 'csrf_invalid', $uuid->toRfc4122());
            $this->addFlash('error', 'Jeton de sécurité expiré. Réessayez.');

            return $this->redirectToEdit($uuid);
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'delete', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label' => 'la suppression définitive d\'un article',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        try {
            $this->handler->__invoke(new DeleteArchivedArticle($uuid));
        } catch (ArticleNotDeletableException $e) {
            $this->audit->actionFailed($admin, 'delete', 'not_archived', $uuid->toRfc4122());
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToEdit($uuid);
        }

        $this->audit->deleted($admin, $uuid->toRfc4122(), $view->slug);
        $this->addFlash('success', 'Article supprimé définitivement.');

        return new RedirectResponse(
            $this->generateUrl('admin_articles_list'),
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

    private function redirectToEdit(Uuid $uuid): RedirectResponse
    {
        return new RedirectResponse(
            $this->generateUrl('admin_article_edit', ['id' => $uuid->toRfc4122()]),
            Response::HTTP_SEE_OTHER,
        );
    }
}
