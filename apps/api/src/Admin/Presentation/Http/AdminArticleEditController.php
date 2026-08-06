<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\EditorialAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Admin\Presentation\Http\Form\ArticleEditData;
use App\Admin\Presentation\Http\Form\FormErrorBag;
use App\Editorial\Application\Command\UpdateDraftArticle;
use App\Editorial\Application\Command\UpdateDraftArticleHandler;
use App\Editorial\Application\Query\GetAdminArticleForEdit;
use App\Editorial\Application\Query\GetAdminArticleForEditHandler;
use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
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
 * Édition d'un brouillon (GET affiche, POST applique la mutation).
 *
 * Le slug est en lecture seule dans le template et jamais rehydraté depuis
 * la requête — voir `ArticleEditData::hydrate()`.
 *
 * Cas d'usage secondaire : cet écran est aussi le point d'atterrissage
 * après création (PRG) et après restauration (Archived → Draft).
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticleEditController extends AbstractController
{
    public function __construct(
        private readonly GetAdminArticleForEditHandler $viewHandler,
        private readonly UpdateDraftArticleHandler $updateHandler,
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

        if ($request->isMethod('GET')) {
            return $this->renderForm($view, ArticleEditData::fromView($view), new FormErrorBag(), Response::HTTP_OK);
        }

        $errors = new FormErrorBag();

        if (!$this->isCsrfTokenValid('article_edit_'.$view->id, (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'edit', 'csrf_invalid', $view->id);
            $errors->addGlobal('Le jeton de sécurité a expiré. Merci de renvoyer le formulaire.');

            return $this->renderForm($view, ArticleEditData::fromView($view), $errors, Response::HTTP_FORBIDDEN);
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'edit', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label' => 'l\'édition de cet article',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        $data = ArticleEditData::hydrate($request, $view);

        try {
            $result = $this->updateHandler->__invoke(new UpdateDraftArticle(
                id: $uuid,
                title: $data->payload->title,
                excerpt: $data->payload->excerpt,
                bodyMarkdown: $data->payload->bodyMarkdown,
                seoTitle: $data->payload->seoTitle,
                seoDescription: $data->payload->seoDescription,
                authorName: $data->payload->authorName,
                authorType: $data->authorType(),
                expertises: $data->expertises(),
            ));
        } catch (ArticleNotEditableException $e) {
            $this->audit->actionFailed($admin, 'edit', 'not_editable', $view->id);
            $errors->addGlobal($e->getMessage());

            return $this->renderForm($view, $data, $errors, Response::HTTP_CONFLICT);
        } catch (MarkdownValidationException $e) {
            $this->audit->actionFailed($admin, 'edit', 'markdown_invalid', $view->id);
            $errors->addField('body_markdown', $e->getMessage());

            return $this->renderForm($view, $data, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ArticleInvariantViolation $e) {
            $this->audit->actionFailed($admin, 'edit', 'invariant_violation', $view->id);
            $errors->addGlobal($e->getMessage());

            return $this->renderForm($view, $data, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($result->mutated) {
            $this->audit->updated($admin, $view->id);
            $this->addFlash('success', 'Brouillon mis à jour.');
        } else {
            $this->audit->updateNoop($admin, $view->id);
            $this->addFlash('info', 'Aucune modification détectée.');
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

    private function renderForm(
        \App\Editorial\Application\Query\AdminArticleEditView $view,
        ArticleEditData $data,
        FormErrorBag $errors,
        int $status,
    ): Response {
        return $this->render('admin/articles/edit.html.twig', [
            'view' => $view,
            'data' => $data,
            'errors' => $errors,
        ], new Response('', $status));
    }
}
