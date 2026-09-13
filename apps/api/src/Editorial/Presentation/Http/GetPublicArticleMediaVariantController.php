<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Http;

use App\Editorial\Application\Media\PublicMediaGateInterface;
use App\Editorial\Application\Media\PublicMediaVariantStreamerInterface;
use App\Editorial\Domain\Clock\ClockInterface;
use App\EditorialMedia\Domain\ImageVariant;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Uid\Uuid;

/**
 * GET /api/media/{id}/{variant} — diffusion publique d'un variant WebP
 * rattaché à un article publié (Phase 9C).
 *
 * Le paramètre `{variant}` est contraint par la route (`card|hero`) : toute
 * valeur hors whitelist retourne 404 directement depuis le routeur Symfony,
 * sans invoquer ce contrôleur.
 *
 * Même gate d'accès que `GetPublicArticleMediaController` :
 *   `PublicMediaGateInterface::isReferencedByPublishedArticle`.
 *
 * Même politique de cache : `public, max-age=3600, must-revalidate`.
 * `immutable` est proscrit pour la même raison que l'original : l'accès
 * public peut être révoqué par archivage de l'article.
 */
#[AsController]
final class GetPublicArticleMediaVariantController
{
    public function __construct(
        private readonly PublicMediaGateInterface $gate,
        private readonly PublicMediaVariantStreamerInterface $variantStreamer,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $editorialLogger,
    ) {
    }

    public function __invoke(string $id, string $variant, Request $request): Response
    {
        $requestId = Uuid::v7()->toRfc4122();

        try {
            $mediaId = Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            return $this->notFound($requestId);
        }

        // La contrainte de route garantit $variant ∈ {'card','hero'}.
        $imageVariant = ImageVariant::from($variant);

        $now = $this->clock->now();

        if (!$this->gate->isReferencedByPublishedArticle($mediaId, $now)) {
            $this->editorialLogger->info('editorial.public_media_variant.not_referenced', [
                'request_id' => $requestId,
                'media_id'   => $mediaId->toRfc4122(),
                'variant'    => $variant,
            ]);

            return $this->notFound($requestId);
        }

        $resource = $this->variantStreamer->openVariant($mediaId, $imageVariant);
        if ($resource === null) {
            return $this->notFound($requestId);
        }

        $response = new StreamedResponse(static function () use ($resource): void {
            $stream = $resource->stream;
            while (!feof($stream)) {
                $chunk = fread($stream, 8192);
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
            }
            fclose($stream);
        });

        $response->setEtag($resource->sha256);
        $response->setLastModified($resource->lastModified);
        $response->headers->set('Content-Type', 'image/webp');
        $response->headers->set('Content-Length', (string) $resource->sizeBytes);
        $response->headers->set('Cache-Control', 'public, max-age=3600, must-revalidate');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Robots-Tag', 'index, follow');
        $response->headers->set('X-Request-Id', $requestId);

        if ($response->isNotModified($request)) {
            $response->headers->set('X-Request-Id', $requestId);
        }

        return $response;
    }

    private function notFound(string $requestId): JsonResponse
    {
        $response = new JsonResponse([
            'status'     => 'error',
            'code'       => 'not_found',
            'request_id' => $requestId,
        ], Response::HTTP_NOT_FOUND);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
