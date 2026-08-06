<?php

declare(strict_types=1);

namespace App\Tests\Admin\Application;

use App\Admin\Application\PasswordPolicy;
use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\Exception\AdminUserInvariantViolation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PasswordPolicyTest extends TestCase
{
    public function testAcceptsPasswordAboveMinimumLength(): void
    {
        PasswordPolicy::assert('CorrectHorseBatteryStaple!', AdminEmail::fromString('alice@example.com'));
        $this->addToAssertionCount(1);
    }

    public function testAcceptsBoundaryMinimumLength(): void
    {
        PasswordPolicy::assert(str_repeat('a', PasswordPolicy::MIN_LENGTH), AdminEmail::fromString('alice@example.com'));
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidPasswords')]
    public function testRejectsInvalidPasswords(string $password, string $reasonHint): void
    {
        $this->expectException(AdminUserInvariantViolation::class);
        PasswordPolicy::assert($password, AdminEmail::fromString('alice@example.com'));
        $this->addToAssertionCount(1); // reasonHint est là pour la lisibilité du provider
        unset($reasonHint);
    }

    /** @return array<string, array{string, string}> */
    public static function invalidPasswords(): array
    {
        return [
            'empty' => ['', 'empty'],
            'whitespace only' => ['            ', 'blank'],
            'too short' => ['short12345', 'below min length'],
            'equal to email lowercase' => ['alice@example.com', 'echoes identifier'],
            'equal to email uppercase' => ['ALICE@EXAMPLE.COM', 'echoes identifier (case)'],
        ];
    }
}
