<?php

declare(strict_types=1);

namespace App\Editorial\Domain;

/**
 * Type d'auteur d'un article.
 *
 * Le front rend `Organization` sans photo/bio, et `Person` avec, ce qui
 * permet de distinguer un article institutionnel d'un billet nominatif.
 */
enum AuthorType: string
{
    case Organization = 'organization';
    case Person = 'person';
}
