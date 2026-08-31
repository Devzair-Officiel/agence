<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\PricingAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Estimator\Application\Pricing\CreatePricingDraft;
use App\Estimator\Application\Pricing\PricingCatalogDefinition;
use App\Estimator\Domain\Pricing\Exception\DuplicatePricingVersionException;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Crée un brouillon tarifaire en clonant la configuration publiée.
 *
 * L'administrateur ne reçoit jamais un formulaire vide : le clone de la version
 * publiée garantit une grille complète à modifier dès la création.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminEstimatorPricingNewController extends AbstractController
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
        private readonly CreatePricingDraft $createDraft,
        private readonly AdminActionRateLimiter $rateLimiter,
        private readonly PricingAdminAuditLogger $audit,
    ) {}

    public function __invoke(Request $request): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $published = $this->repository->findPublished();

        if ($published === null) {
            $this->addFlash('error', 'Aucune configuration publiée disponible pour cloner. Vérifiez que la migration a bien été exécutée.');

            return new RedirectResponse(
                $this->generateUrl('admin_pricing_list'),
                Response::HTTP_SEE_OTHER,
            );
        }

        if ($request->isMethod('GET')) {
            return $this->render('admin/estimator/pricing/new.html.twig', [
                'published_version' => $published->version(),
            ]);
        }

        if (!$this->isCsrfTokenValid('pricing_new', (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'create', 'csrf_invalid');
            $this->addFlash('error', 'Jeton de sécurité expiré. Réessayez.');

            return $this->render('admin/estimator/pricing/new.html.twig', [
                'published_version' => $published->version(),
            ], new Response('', Response::HTTP_FORBIDDEN));
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'create', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label'        => 'la création d\'un brouillon tarifaire',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        $newVersion = trim((string) $request->request->get('version', ''));

        if ($newVersion === '') {
            $this->addFlash('error', 'Le label de version est obligatoire.');

            return $this->render('admin/estimator/pricing/new.html.twig', [
                'published_version' => $published->version(),
            ], new Response('', Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        $definition = PricingCatalogDefinition::fromArray(
            $published->version(),
            $published->currency(),
            $published->configuration(),
        );

        try {
            $draft = $this->createDraft->create(
                newVersion: $newVersion,
                currency:   $published->currency(),
                definition: $definition,
                createdBy:  $admin->id(),
            );
        } catch (DuplicatePricingVersionException $e) {
            $this->addFlash('error', sprintf('Le label de version "%s" est déjà utilisé.', $newVersion));

            return $this->render('admin/estimator/pricing/new.html.twig', [
                'published_version' => $published->version(),
            ], new Response('', Response::HTTP_CONFLICT));
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->render('admin/estimator/pricing/new.html.twig', [
                'published_version' => $published->version(),
            ], new Response('', Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        $this->audit->created($admin, $draft->id()->toRfc4122(), $draft->version());
        $this->addFlash('success', sprintf('Brouillon "%s" créé. Modifiez maintenant la grille tarifaire.', $draft->version()));

        return new RedirectResponse(
            $this->generateUrl('admin_pricing_edit', ['id' => $draft->id()->toRfc4122()]),
            Response::HTTP_SEE_OTHER,
        );
    }
}
