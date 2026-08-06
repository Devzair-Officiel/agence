<?php

declare(strict_types=1);

namespace App\Admin\Domain\Exception;

final class AdminUserNotFoundException extends \RuntimeException
{
    public static function byEmail(string $normalizedEmail): self
    {
        return new self(\sprintf('Aucun administrateur ne correspond à l\'email « %s ».', $normalizedEmail));
    }
}
