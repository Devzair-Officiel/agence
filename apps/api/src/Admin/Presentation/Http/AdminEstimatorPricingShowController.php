<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Presentation\Http\Form\PricingDraftData;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Vue en lecture seule d'une configuration tarifaire (tout statut).
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminEstimatorPricingShowController extends AbstractController
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
    ) {}

    public function __invoke(string $id): Response
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException();
        }

        $config = $this->repository->findById($uuid);

        if ($config === null) {
            throw new NotFoundHttpException();
        }

        return $this->render('admin/estimator/pricing/show.html.twig', [
            'config' => $config,
            'data'   => PricingDraftData::fromConfiguration($config),
        ]);
    }
}
