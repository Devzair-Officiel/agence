<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Http;

use App\Editorial\Application\Query\GetPublishedArticle;
use App\Editorial\Application\Query\GetPublishedArticleHandler;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Uid\Uuid;

/**
 * GET /api/resources/{slug} — détail d'un article publié.
 *
 * - Slug malformé (regex/longueur) → 400 `validation_error`.
 * - Slug bien formé mais article inexistant ou non publié → 404.
 * - Succès → 200 avec cache public court/moyen (60s client, 5 min proxy).
 */
#[AsController]
final class GetPublishedArticleController
{
    public function __construct(
        private readonly GetPublishedArticleHandler $handler,
        private readonly LoggerInterface $editorialLogger,
    ) {
    }

    public function __invoke(string $slug, Request $request): Response
    {
        $requestId = Uuid::v7()->toRfc4122();

        try {
            $query = GetPublishedArticle::fromInput($slug);
        } catch (ArticleInvariantViolation $exception) {
            return $this->error(
                $requestId,
                Response::HTTP_BAD_REQUEST,
                'validation_error',
                [$exception->getMessage()],
            );
        }

        try {
            $view = ($this->handler)($query);
        } catch (ArticleNotFoundException) {
            $this->editorialLogger->info('editorial.detail_not_found', [
                'request_id' => $requestId,
                'slug' => $query->slug->value(),
            ]);

            return $this->error($requestId, Response::HTTP_NOT_FOUND, 'not_found');
        }

        $this->editorialLogger->info('editorial.detail', [
            'request_id' => $requestId,
            'slug' => $query->slug->value(),
        ]);

        $payload = $view->toArray();
        $payload['request_id'] = $requestId;

        $response = new JsonResponse($payload, Response::HTTP_OK);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('Cache-Control', 'public, max-age=60, s-maxage=300');

        return $response;
    }

    /**
     * @param list<string> $errors
     */
    private function error(string $requestId, int $status, string $code, array $errors = []): JsonResponse
    {
        $payload = [
            'status' => 'error',
            'code' => $code,
            'request_id' => $requestId,
        ];
        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        $response = new JsonResponse($payload, $status);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
