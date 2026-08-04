<?php

declare(strict_types=1);

namespace App\Editorial\Domain;

use App\Editorial\Domain\Exception\ArticleNotFoundException;

/**
 * Port du domaine éditorial.
 *
 * L'implémentation par défaut est Doctrine (`DoctrineArticleRepository`).
 * Les tests unitaires de l'application injectent une variante en mémoire
 * (`InMemoryArticleRepository`) pour rester rapides et déterministes.
 *
 * Les deux méthodes de lecture publiques renvoient uniquement des articles
 * dont le statut est `Published`. Les brouillons et archives ne sont jamais
 * exposés par ce port — c'est un choix volontaire pour empêcher un usage
 * accidentel côté API publique.
 */
interface ArticleRepositoryInterface
{
    public function save(Article $article): void;

    /**
     * Résout un article publié par son slug ou lève `ArticleNotFoundException`.
     *
     * @throws ArticleNotFoundException
     */
    public function getPublishedBySlug(ArticleSlug $slug): Article;

    /**
     * Renvoie une page ordonnée par `publishedAt DESC, id DESC`.
     *
     * @param int<1, max>   $page
     * @param int<1, max>   $perPage
     *
     * @return list<Article>
     */
    public function listPublished(int $page, int $perPage): array;

    public function countPublished(): int;
}
