<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\EditorialAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Editorial\Application\Command\RestoreArticle;
use App\Editorial\Application\Command\RestoreArticleHandler;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Editorial\Domain\Exception\InvalidArticleTransitionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Restauration Archived → Draft. POST uniquement. Idempotent quand
 * l'article est déjà en Draft. Refuse net depuis Published (l'agrégat
 * lève `InvalidArticleTransitionException`).
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticleRestoreController extends AbstractController
{
    public function __construct(
        private readonly RestoreArticleHandler $handler,
        private readonly AdminActionRateLimiter $rateLimiter,
        private readonly EditorialAdminAuditLogger $audit,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $uuid = $this->parseUuidOrNotFound($id);

        if (!$this->isCsrfTokenValid('article_restore_'.$uuid->toRfc4122(), (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'restore', 'csrf_invalid', $uuid->toRfc4122());
            $this->addFlash('error', 'Jeton de sécurité expiré. Réessayez.');

            return $this->redirectToList();
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'restore', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label' => 'la restauration d\'un article',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        try {
            $result = $this->handler->__invoke(new RestoreArticle($uuid));
        } catch (ArticleNotFoundException) {
            throw new NotFoundHttpException();
        } catch (InvalidArticleTransitionException $e) {
            $this->audit->actionFailed($admin, 'restore', 'invalid_transition', $uuid->toRfc4122());
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToList();
        }

        if ($result->alreadyDraft) {
            $this->addFlash('info', 'Article déjà en brouillon.');
        } else {
            $this->audit->restored($admin, $uuid->toRfc4122());
            $this->addFlash('success', 'Article restauré comme brouillon.');
        }

        return new RedirectResponse(
            $this->generateUrl('admin_article_edit', ['id' => $uuid->toRfc4122()]),
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

    private function redirectToList(): RedirectResponse
    {
        return new RedirectResponse(
            $this->generateUrl('admin_articles_list'),
            Response::HTTP_SEE_OTHER,
        );
    }
}
