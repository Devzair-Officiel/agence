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
 * Les lectures publiques (`getPublishedBySlug`, `listPublished`,
 * `countPublished`) filtrent doublement : sur `status = Published` ET sur
 * `publishedAt <= $now`. Le second filtre garantit qu'un article publié avec
 * une date de publication future (via la CLI `app:editorial:publish
 * --published-at=...`) ne fuite jamais avant l'instant demandé, même si la
 * commande est exécutée à l'avance.
 *
 * `findBySlug` renvoie l'article correspondant au slug quel que soit son
 * statut (Draft, Published, Archived) — utilisé exclusivement par le pipeline
 * d'import pour détecter les collisions de slug avant écriture.
 */
interface ArticleRepositoryInterface
{
    public function save(Article $article): void;

    /**
     * Résout un article, publié ou non, par son slug. Renvoie `null` si
     * aucun article ne porte ce slug.
     */
    public function findBySlug(ArticleSlug $slug): ?Article;

    /**
     * Résout un article publié dont `publishedAt <= $now` ou lève
     * `ArticleNotFoundException`.
     *
     * @throws ArticleNotFoundException
     */
    public function getPublishedBySlug(ArticleSlug $slug, \DateTimeImmutable $now): Article;

    /**
     * Renvoie une page ordonnée par `publishedAt DESC, id DESC`, restreinte
     * aux articles publiés dont `publishedAt <= $now`.
     *
     * @param int<1, max>   $page
     * @param int<1, max>   $perPage
     *
     * @return list<Article>
     */
    public function listPublished(int $page, int $perPage, \DateTimeImmutable $now): array;

    public function countPublished(\DateTimeImmutable $now): int;
}
