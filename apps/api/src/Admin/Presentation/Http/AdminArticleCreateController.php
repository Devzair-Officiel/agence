<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\EditorialAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Admin\Presentation\Http\Form\ArticleCreateData;
use App\Admin\Presentation\Http\Form\FormErrorBag;
use App\Editorial\Application\Command\CreateDraftArticle;
use App\Editorial\Application\Command\CreateDraftArticleHandler;
use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleSlugAlreadyExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Création d'un brouillon depuis l'administration (GET affiche, POST traite).
 *
 * Sécurité :
 *   - CSRF token `article_create` (voir `_form.html.twig`) ;
 *   - rate limit `admin_write` (60 req / 5 min par admin authentifié) ;
 *   - toute erreur métier (invariant, slug déjà pris, Markdown invalide)
 *     est traduite en messages de formulaire propres — jamais d'exception
 *     brute côté client.
 *
 * Le POST suit le pattern PRG : redirection 302 vers l'écran d'édition du
 * brouillon fraîchement créé en cas de succès (`edit_article` avec l'UUID).
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticleCreateController extends AbstractController
{
    public function __construct(
        private readonly CreateDraftArticleHandler $handler,
        private readonly AdminActionRateLimiter $rateLimiter,
        private readonly EditorialAdminAuditLogger $audit,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        if ($request->isMethod('GET')) {
            return $this->renderForm(ArticleCreateData::empty(), new FormErrorBag(), Response::HTTP_OK);
        }

        $errors = new FormErrorBag();

        if (!$this->isCsrfTokenValid('article_create', (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'create', 'csrf_invalid');
            $errors->addGlobal('Le jeton de sécurité a expiré. Merci de renvoyer le formulaire.');

            return $this->renderForm(ArticleCreateData::empty(), $errors, Response::HTTP_FORBIDDEN);
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'create', $retryAfter);

            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label' => 'la création d\'un article',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        $data = ArticleCreateData::hydrate($request);

        try {
            $result = $this->handler->__invoke(new CreateDraftArticle(
                slug: $data->payload->slug,
                title: $data->payload->title,
                excerpt: $data->payload->excerpt,
                bodyMarkdown: $data->payload->bodyMarkdown,
                seoTitle: $data->payload->seoTitle,
                seoDescription: $data->payload->seoDescription,
                authorName: $data->payload->authorName,
                authorType: $data->authorType(),
                expertises: $data->expertises(),
            ));
        } catch (ArticleSlugAlreadyExistsException $e) {
            $this->audit->actionFailed($admin, 'create', 'slug_conflict');
            $errors->addField('slug', $e->getMessage());

            return $this->renderForm($data, $errors, Response::HTTP_CONFLICT);
        } catch (MarkdownValidationException $e) {
            $this->audit->actionFailed($admin, 'create', 'markdown_invalid');
            $errors->addField('body_markdown', $e->getMessage());

            return $this->renderForm($data, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ArticleInvariantViolation $e) {
            $this->audit->actionFailed($admin, 'create', 'invariant_violation');
            $errors->addGlobal($e->getMessage());

            return $this->renderForm($data, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->audit->created($admin, $result->article->id()->toRfc4122(), $result->article->slug()->value());
        $this->addFlash('success', 'Brouillon créé.');

        return new RedirectResponse(
            $this->generateUrl('admin_article_edit', ['id' => $result->article->id()->toRfc4122()]),
            Response::HTTP_SEE_OTHER,
        );
    }

    private function renderForm(ArticleCreateData $data, FormErrorBag $errors, int $status): Response
    {
        return $this->render('admin/articles/new.html.twig', [
            'data' => $data,
            'errors' => $errors,
        ], new Response('', $status));
    }
}
