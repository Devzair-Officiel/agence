<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Http;

use App\Editorial\Application\Media\PublicMediaGateInterface;
use App\Editorial\Application\Media\PublicMediaStreamerInterface;
use App\Editorial\Domain\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Uid\Uuid;

/**
 * GET /api/media/{id} — diffusion publique d'une image rattachée à un
 * article publié (Phase 9B).
 *
 * Contrat :
 *   - UUID malformé → 404 (aucune divulgation via 400 : on aligne le code
 *     d'erreur pour ne pas signaler qu'un identifiant serait syntaxiquement
 *     valide sans être servi) ;
 *   - Média inconnu OU non référencé par un article publié → 404 ;
 *   - Succès → 200 + `Cache-Control: public, max-age=3600, must-revalidate`
 *     + ETag fort (SHA-256 du média) + `Last-Modified` (createdAt du média) ;
 *   - `If-None-Match` / `If-Modified-Since` correspondant → 304 sans corps.
 *
 * Sécurité :
 *   - Content-Type posé côté serveur depuis `MediaType::mime()`, jamais
 *     depuis un input client (protège contre le MIME sniffing).
 *   - `X-Content-Type-Options: nosniff` + `X-Robots-Tag: index, follow`
 *     (l'image accompagne un contenu SEO, on l'indexe volontairement).
 *   - Le binaire est streamé (`fread` chunks de 8 KiB) pour ne pas charger
 *     un fichier entier de plusieurs MiB en mémoire.
 *
 * Perf/SEO :
 *   - `Cache-Control: public, max-age=3600, must-revalidate` — cache
 *     navigateur/CDN pendant 1 heure, puis revalidation obligatoire via
 *     `If-None-Match`. **`immutable` est proscrit** : les octets d'un média
 *     donné sont bien immuables (sha256 stable), mais l'**accessibilité
 *     publique** de la ressource ne l'est pas. Un article référençant le
 *     média peut être archivé → cette même route doit alors basculer en
 *     404. Sans revalidation, un client garderait un 200 en cache et
 *     continuerait de servir un média qui n'est plus autorisé publiquement.
 *   - `must-revalidate` interdit à un cache intermédiaire de servir une
 *     réponse périmée (`stale-while-revalidate`), garantissant qu'un 404
 *     post-archivage sera propagé au premier hit expiré.
 *   - Le corps du 304 est vide, mais on préserve `X-Request-Id` pour la
 *     corrélation logs.
 */
#[AsController]
final class GetPublicArticleMediaController
{
    public function __construct(
        private readonly PublicMediaGateInterface $gate,
        private readonly PublicMediaStreamerInterface $streamer,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $editorialLogger,
    ) {
    }

    public function __invoke(string $id, Request $request): Response
    {
        $requestId = Uuid::v7()->toRfc4122();

        try {
            $mediaId = Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            return $this->notFound($requestId);
        }

        $now = $this->clock->now();

        if (!$this->gate->isReferencedByPublishedArticle($mediaId, $now)) {
            $this->editorialLogger->info('editorial.public_media.not_referenced', [
                'request_id' => $requestId,
                'media_id' => $mediaId->toRfc4122(),
            ]);

            return $this->notFound($requestId);
        }

        $resource = $this->streamer->open($mediaId);
        if ($resource === null) {
            return $this->notFound($requestId);
        }

        // ETag fort (`"..."` sans `W/`) : le contenu servi est byte-à-byte
        // identique à chaque hit tant que le média existe (SHA-256 stable).
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
        $response->headers->set('Content-Type', $resource->mimeType);
        $response->headers->set('Content-Length', (string) $resource->sizeBytes);
        $response->headers->set('Cache-Control', 'public, max-age=3600, must-revalidate');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Robots-Tag', 'index, follow');
        $response->headers->set('X-Request-Id', $requestId);

        if ($response->isNotModified($request)) {
            // Sur 304, Symfony vide le corps et remet certaines entêtes.
            // On rétablit X-Request-Id pour la corrélation logs.
            $response->headers->set('X-Request-Id', $requestId);
        }

        return $response;
    }

    private function notFound(string $requestId): JsonResponse
    {
        $response = new JsonResponse([
            'status' => 'error',
            'code' => 'not_found',
            'request_id' => $requestId,
        ], Response::HTTP_NOT_FOUND);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
