<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Estimator\Domain\Lead\EstimatorLeadRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_ADMIN')]
final class AdminEstimatorLeadShowController extends AbstractController
{
    public function __construct(
        private readonly EstimatorLeadRepositoryInterface $repository,
    ) {}

    public function __invoke(string $id): Response
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException();
        }

        $lead = $this->repository->findById($uuid);

        if ($lead === null) {
            throw new NotFoundHttpException();
        }

        return $this->render('admin/estimator/leads/show.html.twig', [
            'lead' => $lead,
        ]);
    }
}
