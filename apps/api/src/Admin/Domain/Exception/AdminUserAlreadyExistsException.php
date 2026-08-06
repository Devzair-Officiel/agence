<?php

declare(strict_types=1);

namespace App\Admin\Domain\Exception;

final class AdminUserAlreadyExistsException extends \RuntimeException
{
    public static function withEmail(string $normalizedEmail): self
    {
        return new self(\sprintf('Un administrateur avec l\'email « %s » existe déjà.', $normalizedEmail));
    }
}
