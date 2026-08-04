<?php

declare(strict_types=1);

namespace App\Editorial\Domain\Exception;

/**
 * Le domaine refuse un état invalide (slug malformé, statut incohérent,
 * publishedAt manquant sur un article publié…). Toujours levée depuis
 * l'entité ou un value object — jamais depuis la couche HTTP.
 */
final class ArticleInvariantViolation extends \DomainException
{
}
