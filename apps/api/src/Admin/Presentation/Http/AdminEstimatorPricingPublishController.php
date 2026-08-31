<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\PricingAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Estimator\Application\Pricing\PricingValidationException;
use App\Estimator\Application\Pricing\PublishPricingConfiguration;
use App\Estimator\Domain\Pricing\Exception\InvalidPricingTransitionException;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
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
 * La publication est une action irréversible et atomique :
 * - l'ancienne version publiée devient archived ;
 * - le brouillon ciblé devient published.
 *
 * Un CSRF par brouillon (`pricing_publish_{uuid}`) limite l'impact d'un
 * jeton exfiltré à un seul brouillon.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminEstimatorPricingPublishController extends AbstractController
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
        private readonly PublishPricingConfiguration $publish,
        private readonly AdminActionRateLimiter $rateLimiter,
        private readonly PricingAdminAuditLogger $audit,
    ) {}

    public function __invoke(Request $request, string $id): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $uuid   = $this->parseUuidOrNotFound($id);
        $config = $this->repository->findById($uuid);

        if ($config === null) {
            throw new NotFoundHttpException();
        }

        if (!$this->isCsrfTokenValid('pricing_publish_'.$uuid->toRfc4122(), (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'publish', 'csrf_invalid', $uuid->toRfc4122());
            $this->addFlash('error', 'Jeton de sécurité expiré. Réessayez.');

            return new RedirectResponse(
                $this->generateUrl('admin_pricing_edit', ['id' => $uuid->toRfc4122()]),
                Response::HTTP_SEE_OTHER,
            );
        }

        $limit = $this->rateLimiter->consumePublish($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'publish', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label'        => 'la publication d\'une grille tarifaire',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        $previousPublished = $this->repository->findPublished();

        try {
            $this->publish->publish($uuid, $admin->id());
        } catch (PricingValidationException $e) {
            $this->audit->actionFailed($admin, 'publish', 'validation_failed', $uuid->toRfc4122());
            $this->addFlash('error', sprintf(
                'Publication bloquée : la grille "%s" est incomplète (%d erreur(s)). Corrigez avant de publier.',
                $config->version(),
                count($e->errors()),
            ));

            return new RedirectResponse(
                $this->generateUrl('admin_pricing_edit', ['id' => $uuid->toRfc4122()]),
                Response::HTTP_SEE_OTHER,
            );
        } catch (InvalidPricingTransitionException $e) {
            $this->audit->actionFailed($admin, 'publish', 'invalid_transition', $uuid->toRfc4122());
            $this->addFlash('error', $e->getMessage());

            return new RedirectResponse(
                $this->generateUrl('admin_pricing_list'),
                Response::HTTP_SEE_OTHER,
            );
        }

        $this->audit->published(
            $admin,
            $uuid->toRfc4122(),
            $config->version(),
            $previousPublished?->id()->toRfc4122(),
        );
        $this->addFlash('success', sprintf('Grille tarifaire "%s" publiée avec succès.', $config->version()));

        return new RedirectResponse(
            $this->generateUrl('admin_pricing_list'),
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
