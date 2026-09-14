<?php

declare(strict_types=1);

namespace App\EditorialMedia\Application\Command;

use Symfony\Component\Uid\Uuid;

/**
 * Commande de suppression d'un média éditorial (Phase 9D).
 *
 * Le contrôleur admin résout l'UUID depuis la route et le passe ici.
 * Toute la logique de vérification (média utilisé ?) et de suppression
 * physique appartient au handler.
 */
final class DeleteEditorialMedia
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }
}
