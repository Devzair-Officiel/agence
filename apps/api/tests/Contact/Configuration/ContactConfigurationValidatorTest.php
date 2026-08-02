<?php

declare(strict_types=1);

namespace App\Tests\Contact\Configuration;

use App\Contact\Configuration\ContactConfigurationIssue;
use App\Contact\Configuration\ContactConfigurationValidator;
use PHPUnit\Framework\TestCase;

/**
 * Le validateur est un service pur : ces tests n'ont besoin d'aucun bootstrap
 * Symfony, d'aucun SMTP, d'aucune connexion réseau. On passe les paramètres
 * en clair via le constructeur.
 */
final class ContactConfigurationValidatorTest extends TestCase
{
    public function testValidProdConfigurationHasNoErrors(): void
    {
        $validator = $this->build(
            appEnv: 'prod',
            mailerDsn: 'smtps://user:secret@smtp.example.tld:465',
            fromEmail: 'no-reply@devzair.fr',
            fromName: 'Devzair — Site',
            recipient: 'contact@devzair.fr',
            turnstileEnabled: true,
            turnstileSecret: 'cf_secret',
            originAllowlist: ['https://devzair.fr'],
            trustedProxies: 'REMOTE_ADDR',
        );

        $report = $validator->validate();

        self::assertTrue($report->isValid(), 'La configuration prod complète doit être valide.');
        self::assertSame([], $report->errors());
    }

    public function testEmptyMailerDsnIsAnError(): void
    {
        $report = $this->build(mailerDsn: '')->validate();

        self::assertFalse($report->isValid());
        self::assertContainsCode('mailer_dsn_missing', $report->errors());
    }

    public function testNullMailerDsnIsRejectedInProd(): void
    {
        $report = $this->build(
            appEnv: 'prod',
            mailerDsn: 'null://null',
            turnstileEnabled: true,
            turnstileSecret: 'cf_secret',
        )->validate();

        self::assertFalse($report->isValid());
        self::assertContainsCode('mailer_dsn_null_in_prod', $report->errors());
    }

    public function testNullMailerDsnIsToleratedOutsideProd(): void
    {
        $report = $this->build(
            appEnv: 'dev',
            mailerDsn: 'null://null',
        )->validate();

        self::assertTrue($report->isValid(), 'null:// est admis hors prod (dev/test).');
    }

    public function testPlaintextSmtpIsRejectedInProd(): void
    {
        $report = $this->build(
            appEnv: 'prod',
            mailerDsn: 'smtp://user:secret@smtp.example.tld:25',
            turnstileEnabled: true,
            turnstileSecret: 'cf_secret',
        )->validate();

        self::assertFalse($report->isValid());
        self::assertContainsCode('mailer_dsn_plaintext_in_prod', $report->errors());
    }

    public function testMissingRecipientIsAnError(): void
    {
        $report = $this->build(recipient: null)->validate();

        self::assertContainsCode('recipient_missing', $report->errors());
    }

    public function testInvalidRecipientIsAnError(): void
    {
        $report = $this->build(recipient: 'not-an-email')->validate();

        self::assertContainsCode('recipient_invalid', $report->errors());
    }

    public function testInvalidFromEmailIsAnError(): void
    {
        $report = $this->build(fromEmail: 'devzair')->validate();

        self::assertContainsCode('from_email_invalid', $report->errors());
    }

    public function testSuspiciousFromDomainIsAWarningInProd(): void
    {
        $report = $this->build(
            appEnv: 'prod',
            mailerDsn: 'smtps://user:secret@smtp.example.tld:465',
            fromEmail: 'no-reply@devzair.example',
            recipient: 'contact@devzair.fr',
            turnstileEnabled: true,
            turnstileSecret: 'cf_secret',
        )->validate();

        self::assertTrue($report->isValid(), 'Un domaine suspect ne bloque pas mais alerte.');
        self::assertContainsCode('from_email_suspicious_domain', $report->warnings());
    }

    public function testTurnstileEnabledWithoutSecretIsAnError(): void
    {
        $report = $this->build(
            turnstileEnabled: true,
            turnstileSecret: '',
        )->validate();

        self::assertContainsCode('turnstile_secret_missing', $report->errors());
    }

    public function testTurnstileDisabledInProdIsAWarning(): void
    {
        $report = $this->build(
            appEnv: 'prod',
            mailerDsn: 'smtps://user:secret@smtp.example.tld:465',
            recipient: 'contact@devzair.fr',
            turnstileEnabled: false,
            turnstileSecret: null,
        )->validate();

        self::assertTrue($report->isValid());
        self::assertContainsCode('turnstile_disabled_in_prod', $report->warnings());
    }

    public function testEmptyOriginAllowlistIsAnError(): void
    {
        $report = $this->build(originAllowlist: [])->validate();

        self::assertContainsCode('origin_allowlist_empty', $report->errors());
    }

    public function testMissingTrustedProxiesIsAWarning(): void
    {
        $report = $this->build(trustedProxies: null)->validate();

        self::assertContainsCode('trusted_proxies_missing', $report->warnings());
    }

    public function testIssueMessagesNeverLeakSecrets(): void
    {
        $report = $this->build(
            appEnv: 'prod',
            mailerDsn: 'smtp://alice:supersecretpassword@mailhost.example.tld:25',
            turnstileEnabled: true,
            turnstileSecret: 'very-secret-cloudflare-key',
        )->validate();

        foreach ($report->issues as $issue) {
            self::assertStringNotContainsString('supersecretpassword', $issue->message);
            self::assertStringNotContainsString('very-secret-cloudflare-key', $issue->message);
            self::assertStringNotContainsString('mailhost.example.tld', $issue->message);
        }
    }

    /**
     * @param list<string> $originAllowlist
     */
    private function build(
        string $appEnv = 'dev',
        string $mailerDsn = 'null://null',
        string $fromEmail = 'no-reply@devzair.fr',
        string $fromName = 'Devzair',
        ?string $recipient = 'contact@devzair.fr',
        bool $turnstileEnabled = false,
        ?string $turnstileSecret = null,
        array $originAllowlist = ['http://localhost:3001'],
        ?string $trustedProxies = 'REMOTE_ADDR',
    ): ContactConfigurationValidator {
        return new ContactConfigurationValidator(
            appEnv: $appEnv,
            mailerDsn: $mailerDsn,
            fromEmail: $fromEmail,
            fromName: $fromName,
            recipient: $recipient,
            turnstileEnabled: $turnstileEnabled,
            turnstileSecret: $turnstileSecret,
            originAllowlist: $originAllowlist,
            trustedProxies: $trustedProxies,
        );
    }

    /**
     * @param list<ContactConfigurationIssue> $issues
     */
    private static function assertContainsCode(string $code, array $issues): void
    {
        $codes = array_map(static fn (ContactConfigurationIssue $i) => $i->code, $issues);
        self::assertContains($code, $codes, sprintf('Code "%s" absent des issues [%s].', $code, implode(', ', $codes)));
    }
}
