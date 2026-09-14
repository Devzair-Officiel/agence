<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Application\Storage\MediaVariantStorageInterface;
use App\EditorialMedia\Domain\Exception\MediaAssetNotFoundException;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Miniature authentifiée pour la grille admin (Phase 9D UX).
 *
 * Phase 9C assets : sert le variant card WebP (768×432, plus léger).
 * Legacy assets   : fallback transparent vers l'original.
 *
 * Différence avec AdminMediaPreviewController (Phase 9A) : optimisé pour
 * l'affichage en grille (format card 16:9, Content-Length du variant).
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminMediaCardPreviewController extends AbstractController
{
    public function __construct(
        private readonly MediaAssetRepositoryInterface $repository,
        private readonly MediaStorageInterface $storage,
        private readonly MediaVariantStorageInterface $variantStorage,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $uuid = $this->parseUuidOrNotFound($id);

        try {
            $asset = $this->repository->getById($uuid);
        } catch (MediaAssetNotFoundException) {
            throw new NotFoundHttpException();
        }

        $cardKey = $asset->cardStorageKey();

        if ($cardKey !== null) {
            try {
                $stream = $this->variantStorage->openVariantReadStream($cardKey);
                $mime   = 'image/webp';
                $size   = $asset->cardSizeBytes() ?? $asset->sizeBytes();
            } catch (MediaStorageException) {
                throw new NotFoundHttpException();
            }
        } else {
            try {
                $stream = $this->storage->openReadStream($asset->storageKey());
                $mime   = $asset->mimeType()->mime();
                $size   = $asset->sizeBytes();
            } catch (MediaStorageException) {
                throw new NotFoundHttpException();
            }
        }

        $response = new StreamedResponse(static function () use ($stream): void {
            while (!feof($stream)) {
                $chunk = fread($stream, 8192);
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
            }
            fclose($stream);
        });

        $response->headers->set('Content-Type', $mime);
        $response->headers->set('Content-Length', (string) $size);
        $response->headers->set('Content-Disposition', 'inline');

        return $response;
    }

    private function parseUuidOrNotFound(string $id): Uuid
    {
        try {
            return Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException();
        }
    }
}
