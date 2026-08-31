<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\PricingAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use App\Estimator\Domain\Pricing\PricingConfigurationStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Suppression d'un brouillon tarifaire. POST uniquement.
 *
 * Seuls les brouillons (status = draft) sont supprimables.
 * Les versions publiées et archivées sont immuables.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminEstimatorPricingDeleteController extends AbstractController
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
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

        if (!$this->isCsrfTokenValid('pricing_delete_'.$uuid->toRfc4122(), (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'delete', 'csrf_invalid', $uuid->toRfc4122());
            $this->addFlash('error', 'Jeton de sécurité expiré. Réessayez.');

            return new RedirectResponse(
                $this->generateUrl('admin_pricing_list'),
                Response::HTTP_SEE_OTHER,
            );
        }

        if ($config->status() !== PricingConfigurationStatus::Draft) {
            $this->audit->actionFailed($admin, 'delete', 'not_draft', $uuid->toRfc4122());
            $this->addFlash('error', sprintf(
                'Impossible de supprimer la configuration "%s" : seuls les brouillons sont supprimables.',
                $config->version(),
            ));

            return new RedirectResponse(
                $this->generateUrl('admin_pricing_list'),
                Response::HTTP_SEE_OTHER,
            );
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'delete', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label'        => 'la suppression d\'un brouillon tarifaire',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        $version  = $config->version();
        $configId = $uuid->toRfc4122();

        $this->repository->deleteDraft($config);
        $this->audit->deleted($admin, $configId, $version);
        $this->addFlash('success', sprintf('Brouillon "%s" supprimé.', $version));

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
