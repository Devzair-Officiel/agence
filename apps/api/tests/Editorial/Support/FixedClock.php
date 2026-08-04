<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Support;

use App\Editorial\Domain\Clock\ClockInterface;

/**
 * Horloge déterministe pour les tests unitaires — permet de garantir que
 * `Article::publish()` place toujours `publishedAt` à un instant connu et
 * comparable.
 */
final class FixedClock implements ClockInterface
{
    private \DateTimeImmutable $moment;

    public function __construct(string $moment = '2026-08-03T10:00:00+00:00')
    {
        $this->moment = new \DateTimeImmutable($moment);
    }

    public function now(): \DateTimeImmutable
    {
        return $this->moment;
    }

    public function advance(string $modifier): void
    {
        $advanced = $this->moment->modify($modifier);
        if ($advanced === false) {
            throw new \InvalidArgumentException(\sprintf('Modificateur invalide : %s', $modifier));
        }
        $this->moment = $advanced;
    }
}
