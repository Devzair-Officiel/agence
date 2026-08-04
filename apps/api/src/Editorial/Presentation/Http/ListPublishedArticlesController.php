<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Http;

use App\Editorial\Application\Query\ListPublishedArticles;
use App\Editorial\Application\Query\ListPublishedArticlesHandler;
use App\Editorial\Application\View\ArticleSummaryView;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Uid\Uuid;

/**
 * GET /api/resources — liste paginée des articles publiés.
 *
 * Pipeline :
 * 1. Génère un Request-Id (UUID v7) pour la corrélation logs/réponse.
 * 2. Parse `page` / `per_page` depuis la query string, valide via
 *    `ListPublishedArticles::fromInputs()` → 400 si invalide.
 * 3. Délègue au handler qui renvoie items + pagination.
 * 4. Ajoute `Cache-Control: public, max-age=60, s-maxage=300` pour un cache
 *    court côté client et plus long côté reverse proxy.
 *
 * Le contrat JSON est stable : `{ items: [...], pagination: {...}, request_id }`.
 */
#[AsController]
final class ListPublishedArticlesController
{
    public function __construct(
        private readonly ListPublishedArticlesHandler $handler,
        private readonly LoggerInterface $editorialLogger,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $requestId = Uuid::v7()->toRfc4122();

        $rawPage = $request->query->get('page', '1');
        $rawPerPage = $request->query->get('per_page');

        if (!is_string($rawPage) || !ctype_digit($rawPage)) {
            return $this->validationError($requestId, 'page doit être un entier positif.');
        }
        $page = (int) $rawPage;

        $perPage = null;
        if ($rawPerPage !== null) {
            if (!is_string($rawPerPage) || !ctype_digit($rawPerPage)) {
                return $this->validationError($requestId, 'per_page doit être un entier positif.');
            }
            $perPage = (int) $rawPerPage;
        }

        try {
            $query = ListPublishedArticles::fromInputs($page, $perPage);
        } catch (ArticleInvariantViolation $exception) {
            return $this->validationError($requestId, $exception->getMessage());
        }

        $result = ($this->handler)($query);

        $this->editorialLogger->info('editorial.list', [
            'request_id' => $requestId,
            'page' => $query->page,
            'per_page' => $query->perPage,
            'total' => $result['pagination']->total,
        ]);

        $payload = [
            'items' => array_map(
                static fn (ArticleSummaryView $view): array => $view->toArray(),
                $result['items'],
            ),
            'pagination' => $result['pagination']->toArray(),
            'request_id' => $requestId,
        ];

        $response = new JsonResponse($payload, Response::HTTP_OK);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('Cache-Control', 'public, max-age=60, s-maxage=300');

        return $response;
    }

    private function validationError(string $requestId, string $message): JsonResponse
    {
        $response = new JsonResponse([
            'status' => 'error',
            'code' => 'validation_error',
            'errors' => [$message],
            'request_id' => $requestId,
        ], Response::HTTP_BAD_REQUEST);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
