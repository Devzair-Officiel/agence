<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Infrastructure\Markdown;

use App\Editorial\Infrastructure\Markdown\MarkdownSecurityPolicy;
use PHPUnit\Framework\TestCase;

final class MarkdownSecurityPolicyTest extends TestCase
{
    public function testEnvironmentEnforcesSecureConfiguration(): void
    {
        $env = (new MarkdownSecurityPolicy())->environment();
        $config = $env->getConfiguration();

        self::assertSame('strip', $config->get('html_input'));
        self::assertFalse($config->get('allow_unsafe_links'));
        self::assertSame(
            MarkdownSecurityPolicy::MAX_NESTING_LEVEL,
            $config->get('max_nesting_level'),
        );
        self::assertSame(
            MarkdownSecurityPolicy::MAX_DELIMITERS_PER_LINE,
            $config->get('max_delimiters_per_line'),
        );
    }
}
