<?php

declare(strict_types=1);

namespace App\Contact\Security;

use Symfony\Component\HttpFoundation\Request;

/**
 * Vérifie l'en-tête Origin d'une requête contre une liste blanche stricte.
 *
 * Aucune valeur "*" acceptée : la politique CSRF stateless (ADR-007) exige
 * une correspondance exacte. Les requêtes sans Origin sont rejetées (une
 * requête POST cross-origin légitime en émet toujours un ; les serveurs
 * internes utilisent /api/health sans POST).
 */
final class OriginAllowlist
{
    /** @var list<string> */
    private array $normalized;

    /**
     * @param list<string> $allowlist
     */
    public function __construct(array $allowlist)
    {
        $this->normalized = array_values(array_filter(array_map(
            static fn (string $origin): string => rtrim(trim($origin), '/'),
            $allowlist,
        )));
    }

    public function isAllowed(Request $request): bool
    {
        $origin = $request->headers->get('Origin');

        if ($origin === null || $origin === '') {
            return false;
        }

        return \in_array(rtrim($origin, '/'), $this->normalized, true);
    }
}
