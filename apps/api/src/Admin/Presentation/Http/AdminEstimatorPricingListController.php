<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminEstimatorPricingListController extends AbstractController
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $repository,
    ) {}

    public function __invoke(): Response
    {
        return $this->render('admin/estimator/pricing/list.html.twig', [
            'configurations' => $this->repository->listAll(),
        ]);
    }
}
