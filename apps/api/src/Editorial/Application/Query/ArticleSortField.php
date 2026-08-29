<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

enum ArticleSortField: string
{
    case Id        = 'id';
    case Title     = 'title';
    case Status    = 'status';
    case UpdatedAt = 'updated_at';

    public function toDoctrineAlias(): string
    {
        return match($this) {
            self::Id        => 'a.id',
            self::Title     => 'a.title',
            self::Status    => 'a.status',
            self::UpdatedAt => 'a.updatedAt',
        };
    }

    public function secondaryAlias(): string
    {
        return 'a.id';
    }
}
