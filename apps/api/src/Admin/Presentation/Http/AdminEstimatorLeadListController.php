<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Estimator\Domain\Lead\EstimatorLeadRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminEstimatorLeadListController extends AbstractController
{
    public function __construct(
        private readonly EstimatorLeadRepositoryInterface $repository,
    ) {}

    public function __invoke(): Response
    {
        return $this->render('admin/estimator/leads/list.html.twig', [
            'leads' => $this->repository->listAll(),
        ]);
    }
}
