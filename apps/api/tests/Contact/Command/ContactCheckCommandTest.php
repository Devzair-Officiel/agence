<?php

declare(strict_types=1);

namespace App\Tests\Contact\Command;

use App\Contact\Command\ContactCheckCommand;
use App\Contact\Configuration\ContactConfigurationValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * La commande n'a pas de logique métier propre : elle wrappe
 * {@see ContactConfigurationValidator}. On teste ici le contrat public :
 * code de sortie 0 sur configuration valide, code de sortie 1 sur erreur,
 * et absence de fuite des secrets fournis en entrée.
 */
final class ContactCheckCommandTest extends TestCase
{
    public function testExitsZeroWhenConfigIsValid(): void
    {
        $tester = $this->build($this->validValidator());

        $exitCode = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode, sprintf(
            "La commande doit retourner 0 pour une config valide ; sortie :\n%s",
            $tester->getDisplay(),
        ));
        self::assertStringContainsString('Diagnostic du pipeline de contact', $tester->getDisplay());
        self::assertStringContainsString('Configuration OK', $tester->getDisplay());
    }

    public function testExitsFailureAndListsCodesWhenConfigHasErrors(): void
    {
        $validator = new ContactConfigurationValidator(
            appEnv: 'prod',
            mailerDsn: 'null://null',
            fromEmail: '',
            fromName: '',
            recipient: null,
            turnstileEnabled: true,
            turnstileSecret: '',
            originAllowlist: [],
            trustedProxies: null,
        );

        $tester = $this->build($validator);
        $exitCode = $tester->execute([]);

        self::assertSame(Command::FAILURE, $exitCode);
        $display = $tester->getDisplay();
        self::assertStringContainsString('mailer_dsn_null_in_prod', $display);
        self::assertStringContainsString('recipient_missing', $display);
        self::assertStringContainsString('from_email_missing', $display);
        self::assertStringContainsString('turnstile_secret_missing', $display);
        self::assertStringContainsString('origin_allowlist_empty', $display);
    }

    public function testOutputDoesNotLeakDsnOrTurnstileSecret(): void
    {
        $validator = new ContactConfigurationValidator(
            appEnv: 'prod',
            mailerDsn: 'smtp://alice:supersecret_password@mailhost.example.tld:25',
            fromEmail: 'no-reply@devzair.fr',
            fromName: 'Devzair',
            recipient: 'contact@devzair.fr',
            turnstileEnabled: true,
            turnstileSecret: 'topsecret_cloudflare_key_do_not_leak',
            originAllowlist: ['https://devzair.fr'],
            trustedProxies: 'REMOTE_ADDR',
        );

        $tester = $this->build($validator);
        $tester->execute([]);
        $display = $tester->getDisplay();

        self::assertStringNotContainsString('supersecret_password', $display);
        self::assertStringNotContainsString('topsecret_cloudflare_key_do_not_leak', $display);
        self::assertStringNotContainsString('mailhost.example.tld', $display);
    }

    private function validValidator(): ContactConfigurationValidator
    {
        return new ContactConfigurationValidator(
            appEnv: 'dev',
            mailerDsn: 'null://null',
            fromEmail: 'no-reply@devzair.fr',
            fromName: 'Devzair',
            recipient: 'contact@devzair.fr',
            turnstileEnabled: false,
            turnstileSecret: null,
            originAllowlist: ['http://localhost:3001'],
            trustedProxies: 'REMOTE_ADDR',
        );
    }

    private function build(ContactConfigurationValidator $validator): CommandTester
    {
        return new CommandTester(new ContactCheckCommand($validator));
    }
}
