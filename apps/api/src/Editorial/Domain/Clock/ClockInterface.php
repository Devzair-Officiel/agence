<?php

declare(strict_types=1);

namespace App\Editorial\Domain\Clock;

/**
 * Abstraction du temps courant.
 *
 * En dehors des tests, l'unique implémentation est `SystemClock`. Aux tests,
 * on injecte un `FixedClock` pour rendre déterministe les invariants qui
 * dépendent de « maintenant » (`Article::publish()`).
 */
interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
