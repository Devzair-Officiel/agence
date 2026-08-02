<?php

declare(strict_types=1);

namespace App\Contact\Configuration;

/**
 * Vérifie la cohérence des paramètres du pipeline de contact **sans I/O**.
 *
 * Ce service ne se connecte à aucun SMTP, ne joint jamais Cloudflare :
 * il lit les paramètres passés au constructeur et signale les incohérences
 * (recipient manquant, DSN null:// en prod, Turnstile activé sans secret,
 * origin allowlist vide, etc.). Il est appelé :
 * - par la commande {@see \App\Contact\Command\ContactCheckCommand} pour un
 *   diagnostic Ops sans effet de bord ;
 * - potentiellement en warmup pour bloquer un déploiement mal configuré.
 *
 * Aucun message d'issue ne contient de secret, de DSN complet ni d'adresse
 * email intégrale : la sortie est safe à coller dans un ticket ou un log.
 */
final class ContactConfigurationValidator
{
    public function __construct(
        private readonly string $appEnv,
        private readonly string $mailerDsn,
        private readonly string $fromEmail,
        private readonly string $fromName,
        private readonly ?string $recipient,
        private readonly bool $turnstileEnabled,
        private readonly ?string $turnstileSecret,
        /** @var list<string> */
        private readonly array $originAllowlist,
        private readonly ?string $trustedProxies,
    ) {
    }

    public function validate(): ContactConfigurationReport
    {
        $issues = [];
        $isProd = $this->appEnv === 'prod';

        // MAILER_DSN
        if ($this->mailerDsn === '') {
            $issues[] = ContactConfigurationIssue::error(
                'mailer_dsn_missing',
                'MAILER_DSN',
                'MAILER_DSN est vide : aucun transport mail configuré.',
            );
        } elseif ($isProd && str_starts_with($this->mailerDsn, 'null://')) {
            $issues[] = ContactConfigurationIssue::error(
                'mailer_dsn_null_in_prod',
                'MAILER_DSN',
                'MAILER_DSN utilise le transport null:// en production : aucun email ne sera envoyé.',
            );
        } elseif ($isProd && $this->isPlaintextSmtp($this->mailerDsn)) {
            $issues[] = ContactConfigurationIssue::error(
                'mailer_dsn_plaintext_in_prod',
                'MAILER_DSN',
                'MAILER_DSN utilise smtp:// (non chiffré) en production : utilisez smtps:// (TLS implicite) ou activez STARTTLS.',
            );
        }

        // Recipient
        if ($this->recipient === null || $this->recipient === '') {
            $issues[] = ContactConfigurationIssue::error(
                'recipient_missing',
                'CONTACT_RECIPIENT',
                'CONTACT_RECIPIENT est vide : les messages entrants ne pourront être remis à personne.',
            );
        } elseif (!$this->isValidEmail($this->recipient)) {
            $issues[] = ContactConfigurationIssue::error(
                'recipient_invalid',
                'CONTACT_RECIPIENT',
                'CONTACT_RECIPIENT ne ressemble pas à une adresse email valide.',
            );
        }

        // Sender email
        if ($this->fromEmail === '') {
            $issues[] = ContactConfigurationIssue::error(
                'from_email_missing',
                'CONTACT_FROM_EMAIL',
                'CONTACT_FROM_EMAIL est vide : impossible de définir un expéditeur.',
            );
        } elseif (!$this->isValidEmail($this->fromEmail)) {
            $issues[] = ContactConfigurationIssue::error(
                'from_email_invalid',
                'CONTACT_FROM_EMAIL',
                'CONTACT_FROM_EMAIL ne ressemble pas à une adresse email valide.',
            );
        } elseif ($isProd && $this->hasSuspiciousDomain($this->fromEmail)) {
            $issues[] = ContactConfigurationIssue::warning(
                'from_email_suspicious_domain',
                'CONTACT_FROM_EMAIL',
                'CONTACT_FROM_EMAIL semble utiliser un domaine de test/exemple (example, localhost, test).',
            );
        }

        // Sender name
        if (trim($this->fromName) === '') {
            $issues[] = ContactConfigurationIssue::error(
                'from_name_missing',
                'CONTACT_FROM_NAME',
                'CONTACT_FROM_NAME est vide : l\'expéditeur n\'aura pas de nom lisible dans les clients mail.',
            );
        }

        // Turnstile
        if ($this->turnstileEnabled) {
            if ($this->turnstileSecret === null || $this->turnstileSecret === '') {
                $issues[] = ContactConfigurationIssue::error(
                    'turnstile_secret_missing',
                    'TURNSTILE_SECRET',
                    'TURNSTILE_ENABLED=true mais TURNSTILE_SECRET est vide : la vérification échouera systématiquement.',
                );
            }
        } elseif ($isProd) {
            $issues[] = ContactConfigurationIssue::warning(
                'turnstile_disabled_in_prod',
                'TURNSTILE_ENABLED',
                'Turnstile est désactivé en production : le formulaire fonctionne mais l\'anti-abus dépend uniquement du rate limit et de l\'allowlist Origin.',
            );
        }

        // Origin allowlist
        if ($this->originAllowlist === []) {
            $issues[] = ContactConfigurationIssue::error(
                'origin_allowlist_empty',
                'CONTACT_ORIGIN_ALLOWLIST',
                'CONTACT_ORIGIN_ALLOWLIST est vide : toutes les requêtes seront rejetées en 403.',
            );
        }

        // Trusted proxies
        if ($this->trustedProxies === null || $this->trustedProxies === '') {
            $issues[] = ContactConfigurationIssue::warning(
                'trusted_proxies_missing',
                'TRUSTED_PROXIES',
                'TRUSTED_PROXIES est vide : Symfony ne fera confiance à aucun proxy pour lire X-Forwarded-For (rate limit par IP potentiellement erroné derrière Caddy).',
            );
        }

        return new ContactConfigurationReport($issues);
    }

    private function isValidEmail(string $value): bool
    {
        return filter_var($value, \FILTER_VALIDATE_EMAIL) !== false;
    }

    private function hasSuspiciousDomain(string $email): bool
    {
        $atPos = strrpos($email, '@');

        if ($atPos === false) {
            return false;
        }

        $domain = strtolower(substr($email, $atPos + 1));

        foreach (['example.com', 'example.org', 'example.net', '.example', '.test', '.local', 'localhost'] as $marker) {
            if (str_ends_with($domain, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function isPlaintextSmtp(string $dsn): bool
    {
        // On rejette smtp:// non chiffré. smtps:// (TLS implicite) est OK.
        // sendmail://, native://, autres transports non-SMTP : hors périmètre.
        return str_starts_with($dsn, 'smtp://');
    }
}
