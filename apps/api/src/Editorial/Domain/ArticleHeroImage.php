<?php

declare(strict_types=1);

namespace App\Editorial\Domain;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use Symfony\Component\Uid\Uuid;

/**
 * Value Object éditorial représentant l'image principale d'un article.
 *
 * Conceptuellement, il contient :
 *   - `mediaAssetId` : référence stable vers l'agrégat `MediaAsset` (contexte
 *     `EditorialMedia`). Un Uuid — pas un objet Doctrine, pas un `storageKey`,
 *     pas une URL. L'agrégat `Article` reste ainsi ignorant du filesystem et
 *     du stockage physique du média ;
 *   - `altText` : chaîne obligatoire, saisie éditorialement (jamais dérivée
 *     du filename, du slug ou du titre). Longueur bornée pour éviter qu'un
 *     texte alternatif ne serve de champ libre indésirable.
 *
 * Contrat couple :
 *   image = null ↔ alt = null
 *   image ≠ null ↔ alt ≠ null (et non vide)
 *
 * Ce VO n'est présent que si les deux valeurs sont posées ; l'absence
 * d'image est représentée côté agrégat par `null`.
 *
 * L'alt text :
 *   - est du texte simple (pas de Markdown, pas de HTML) ;
 *   - est trimé côté domaine (les whitespaces de bord ne sont jamais
 *     significatifs et brouilleraient les no-op) ;
 *   - est borné à 300 caractères — permet d'écrire une phrase descriptive
 *     complète sans transformer l'attribut alt en champ libre.
 */
final class ArticleHeroImage
{
    private const ALT_MIN_LENGTH = 1;
    private const ALT_MAX_LENGTH = 300;

    private function __construct(
        private readonly Uuid $mediaAssetId,
        private readonly string $altText,
    ) {
    }

    /**
     * Construit un VO valide. Toute violation d'invariant lève une
     * `ArticleInvariantViolation` — la couche présentation traduit le
     * message pour l'utilisateur.
     */
    public static function create(Uuid $mediaAssetId, string $altText): self
    {
        $trimmed = trim($altText);
        $length = mb_strlen($trimmed);

        if ($length < self::ALT_MIN_LENGTH) {
            throw new ArticleInvariantViolation(
                'Le texte alternatif de l\'image principale est obligatoire.',
            );
        }

        if ($length > self::ALT_MAX_LENGTH) {
            throw new ArticleInvariantViolation(\sprintf(
                'Le texte alternatif ne peut pas dépasser %d caractères (actuel : %d).',
                self::ALT_MAX_LENGTH,
                $length,
            ));
        }

        return new self($mediaAssetId, $trimmed);
    }

    public function mediaAssetId(): Uuid
    {
        return $this->mediaAssetId;
    }

    public function altText(): string
    {
        return $this->altText;
    }

    /**
     * Comparaison sémantique — deux VO sont équivalents s'ils portent la
     * même référence média et le même texte alternatif. Utilisée par
     * l'agrégat pour détecter les no-op (préservation de `updatedAt`).
     */
    public function equals(self $other): bool
    {
        return $this->mediaAssetId->equals($other->mediaAssetId)
            && $this->altText === $other->altText;
    }
}
