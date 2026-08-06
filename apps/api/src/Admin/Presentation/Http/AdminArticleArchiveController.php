<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\EditorialAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Editorial\Application\Command\ArchiveArticle;
use App\Editorial\Application\Command\ArchiveArticleHandler;
use App\Editorial\Application\Query\GetAdminArticleForEdit;
use App\Editorial\Application\Query\GetAdminArticleForEditHandler;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Archivage d'un article. POST uniquement. Le libellé du bouton dans le
 * template est explicite (« Archiver et retirer du site public ») — pas de
 * dialog JS de confirmation (CSP interdit `script-src 'self'`).
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticleArchiveController extends AbstractController
{
    public function __construct(
        private readonly ArchiveArticleHandler $handler,
        private readonly GetAdminArticleForEditHandler $viewHandler,
        private readonly AdminActionRateLimiter $rateLimiter,
        private readonly EditorialAdminAuditLogger $audit,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $uuid = $this->parseUuidOrNotFound($id);

        if (!$this->isCsrfTokenValid('article_archive_'.$uuid->toRfc4122(), (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'archive', 'csrf_invalid', $uuid->toRfc4122());
            $this->addFlash('error', 'Jeton de sécurité expiré. Réessayez.');

            return $this->redirectToEdit($uuid);
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'archive', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label' => 'l\'archivage d\'un article',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        try {
            $view = $this->viewHandler->__invoke(new GetAdminArticleForEdit($uuid));
        } catch (ArticleNotFoundException) {
            throw new NotFoundHttpException();
        }

        $priorStatus = $view->status;

        $result = $this->handler->__invoke(new ArchiveArticle($uuid));

        if ($result->alreadyArchived) {
            $this->addFlash('info', 'Article déjà archivé.');
        } else {
            $this->audit->archived($admin, $uuid->toRfc4122(), $priorStatus);
            $this->addFlash('success', 'Article archivé et retiré du site public.');
        }

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
