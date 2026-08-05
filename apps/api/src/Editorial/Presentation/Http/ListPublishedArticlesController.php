<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Http;

use App\Editorial\Application\Query\ListPublishedArticles;
use App\Editorial\Application\Query\ListPublishedArticlesHandler;
use App\Editorial\Application\View\ArticleSummaryView;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Presentation\Http\ConditionalCache\ArticleListETag;
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
 * 4. Calcule un ETag faible sur (page, perPage, total, id:updatedAt de
 *    chaque item) et renvoie 304 si le client fournit un `If-None-Match`
 *    correspondant. Le `X-Request-Id` est préservé sur 304 pour la
 *    corrélation logs, même sans corps.
 * 5. Ajoute `Cache-Control: public, max-age=60, s-maxage=300` pour un cache
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
        $etag = ArticleListETag::forPage($result['items'], $result['pagination']);
        $response->setEtag($etag, weak: true);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('Cache-Control', 'public, max-age=60, s-maxage=300');

        if ($response->isNotModified($request)) {
            // isNotModified() vide le corps et positionne 304, mais réécrit
            // les entêtes conservables. On remet explicitement X-Request-Id
            // pour la corrélation logs même quand aucun corps n'est renvoyé.
            $response->headers->set('X-Request-Id', $requestId);
        }

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
