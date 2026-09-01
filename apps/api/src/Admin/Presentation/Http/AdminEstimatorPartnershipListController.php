<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Estimator\Domain\Partnership\EstimatorPartnershipRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminEstimatorPartnershipListController extends AbstractController
{
    public function __construct(
        private readonly EstimatorPartnershipRepositoryInterface $repository,
    ) {}

    public function __invoke(): Response
    {
        return $this->render('admin/estimator/partnerships/list.html.twig', [
            'proposals' => $this->repository->listAll(),
        ]);
    }
}
