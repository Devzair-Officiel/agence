<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Admin\Infrastructure\Logging\PricingAdminAuditLogger;
use App\Admin\Infrastructure\Security\AdminActionRateLimiter;
use App\Admin\Presentation\Http\Form\FormErrorBag;
use App\Admin\Presentation\Http\Form\PricingDraftData;
use App\Estimator\Application\Pricing\UpdatePricingDraft;
use App\Estimator\Domain\Pricing\Exception\PricingConfigurationImmutableException;
use App\Estimator\Domain\Pricing\PricingConfiguration;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Édition d'un brouillon tarifaire.
 *
 * GET affiche la grille complète dans un formulaire structuré par section.
 * POST applique les modifications et retourne les avertissements de validation
 * (une grille incomplète peut être sauvegardée — seule la publication bloque).
 * Seul un brouillon est éditable : published et archived retournent 409.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminEstimatorPricingEditController extends AbstractController
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
        private readonly UpdatePricingDraft $updateDraft,
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

        $data   = PricingDraftData::fromConfiguration($config);
        $errors = new FormErrorBag();

        if ($request->isMethod('GET')) {
            return $this->renderForm($config, $data, $errors, [], Response::HTTP_OK);
        }

        if (!$this->isCsrfTokenValid('pricing_edit_'.$uuid->toRfc4122(), (string) $request->request->get('_csrf_token'))) {
            $this->audit->actionFailed($admin, 'edit', 'csrf_invalid', $uuid->toRfc4122());
            $errors->addGlobal('Le jeton de sécurité a expiré. Merci de renvoyer le formulaire.');

            return $this->renderForm($config, $data, $errors, [], Response::HTTP_FORBIDDEN);
        }

        $limit = $this->rateLimiter->consumeWrite($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->rateLimited($admin, 'edit', $retryAfter);
            $response = $this->render('admin/articles/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
                'action_label'        => 'l\'édition de la grille tarifaire',
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        $data = PricingDraftData::hydrate($request, $config->version());

        try {
            $warnings = $this->updateDraft->update($uuid, $data->toDefinition());
        } catch (PricingConfigurationImmutableException $e) {
            $this->audit->actionFailed($admin, 'edit', 'immutable', $uuid->toRfc4122());
            $errors->addGlobal($e->getMessage());

            return $this->renderForm($config, $data, $errors, [], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            $errors->addGlobal($e->getMessage());

            return $this->renderForm($config, $data, $errors, [], Response::HTTP_NOT_FOUND);
        }

        $this->audit->updated($admin, $uuid->toRfc4122(), $config->version());

        if ($warnings !== []) {
            $this->addFlash('info', sprintf(
                'Grille sauvegardée avec %d avertissement(s) de validation — corriger avant de publier.',
                count($warnings),
            ));
        } else {
            $this->addFlash('success', 'Grille tarifaire mise à jour.');
        }

        return new RedirectResponse(
            $this->generateUrl('admin_pricing_edit', ['id' => $uuid->toRfc4122()]),
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
        PricingConfiguration $config,
        PricingDraftData $data,
        FormErrorBag $errors,
        array $warnings,
        int $status,
    ): Response {
        return $this->render('admin/estimator/pricing/edit.html.twig', [
            'config'   => $config,
            'data'     => $data,
            'errors'   => $errors,
            'warnings' => $warnings,
        ], new Response('', $status));
    }
}
