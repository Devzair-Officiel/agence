<?php

declare(strict_types=1);

namespace App\Contact\Security;

final readonly class TurnstileVerdict
{
    public function __construct(
        public bool $success,
        public string $reason = '',
    ) {
    }

    public static function ok(): self
    {
        return new self(true);
    }

    public static function failure(string $reason): self
    {
        return new self(false, $reason);
    }
}
