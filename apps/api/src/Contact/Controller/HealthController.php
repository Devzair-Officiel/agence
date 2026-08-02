<?php

declare(strict_types=1);

namespace App\Contact\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * Endpoint léger utilisé par Caddy et la CI pour vérifier que l'API répond.
 * Ne consomme aucune ressource : pas de DB, pas d'auth, pas de rate limit.
 */
#[AsController]
final class HealthController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }
}
