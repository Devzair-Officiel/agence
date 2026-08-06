<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\EditorialAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Editorial\Application\Command\PublishDraftArticle;
use App\Editorial\Application\Command\PublishDraftArticleHandler;
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
 * Transition Draft → Published. POST uniquement.
 *
 * Utilise le limiteur `admin_publish` (plus serré que `admin_write`) et un
 * jeton CSRF spécifique `article_publish_{uuid}` — un jeton par article
 * signifie qu'un jeton exfiltré ne permet de publier qu'un seul article,
 * pas l'intégralité du catalogue.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticlePublishController extends AbstractController
{
    public function __construct(
        private readonly PublishDraftArticleHandler $handler,
        private readonly AdminActionRateLimiter $rateLimiter,
        private readonly EditorialAdminAuditLogger $audit,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $uuid = $this->parseUuidOrNotFound($id);

        if (!$this->isCsrfTokenValid('article_publish_'.$uuid->toRfc4122(), (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'publish', 'csrf_invalid', $uuid->toRfc4122());
            $this->addFlash('error', 'Jeton de sécurité expiré. Réessayez.');

            return $this->redirectToEdit($uuid);
        }

        $limit = $this->rateLimiter->consumePublish($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'publish', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label' => 'la publication d\'un article',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        try {
            $result = $this->handler->__invoke(new PublishDraftArticle($uuid));
        } catch (ArticleNotFoundException) {
            throw new NotFoundHttpException();
        } catch (InvalidArticleTransitionException $e) {
            $this->audit->actionFailed($admin, 'publish', 'invalid_transition', $uuid->toRfc4122());
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToEdit($uuid);
        }

        $this->audit->published(
            $admin,
            $result->article->id()->toRfc4122(),
            $result->article->slug()->value(),
        );
        $this->addFlash('success', 'Article publié.');

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
