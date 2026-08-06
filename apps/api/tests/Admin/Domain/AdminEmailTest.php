<?php

declare(strict_types=1);

namespace App\Tests\Admin\Domain;

use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\Exception\AdminUserInvariantViolation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdminEmailTest extends TestCase
{
    public function testDisplayPreservesCaseButNormalizedIsLowercased(): void
    {
        $email = AdminEmail::fromString('  Alice.SMITH@Example.COM  ');

        self::assertSame('Alice.SMITH@Example.COM', $email->display());
        self::assertSame('alice.smith@example.com', $email->normalized());
    }

    public function testTrimsSurroundingWhitespace(): void
    {
        $email = AdminEmail::fromString("\tbob@devzair.local\n");

        self::assertSame('bob@devzair.local', $email->display());
    }

    #[DataProvider('invalidValues')]
    public function testRejectsInvalidValues(string $raw): void
    {
        $this->expectException(AdminUserInvariantViolation::class);
        AdminEmail::fromString($raw);
    }

    /** @return array<string, array{string}> */
    public static function invalidValues(): array
    {
        return [
            'empty' => [''],
            'whitespace only' => ['   '],
            'no @' => ['aliceatexample.com'],
            'no domain part' => ['alice@'],
            'no local part' => ['@example.com'],
            'no TLD' => ['alice@example'],
            'contains space' => ['alice @example.com'],
            'too long' => [str_repeat('a', 176).'@x.io'],
        ];
    }
}
