<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Console;

use App\Estimator\Application\Pricing\PricingCatalogDefinition;
use App\Estimator\Application\Pricing\PricingConfigurationValidator;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use App\Estimator\Domain\Pricing\PricingConfigurationStatus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande de pré-déploiement : vérifie que l'environnement est prêt pour
 * recevoir du trafic estimateur en production.
 *
 * Codes de sortie :
 *   0 = OK (toutes les gates sont vertes, les warnings n'échouent pas)
 *   1 = FAIL (au moins une gate critique est rouge)
 *
 * Ne révèle jamais :
 *   - le MAILER_DSN complet (secret) ;
 *   - les adresses email complètes ;
 *   - les credentials de base de données.
 *
 * Comportement par environnement :
 *   - prod : APP_ADMIN_BASE_URL vide → FAIL, CONTACT_RECIPIENT vide → FAIL,
 *             pricing active absente → FAIL, MAILER_DSN null → WARN.
 *   - dev/test : valeurs de développement/fixtures acceptables → WARN uniquement.
 */
#[AsCommand(
    name: 'app:estimator:preflight',
    description: 'Vérifie que la configuration estimateur est prête pour la production.',
)]
final class EstimatorPreflightCommand extends Command
{
    public function __construct(
        private readonly PricingConfigurationRepositoryInterface $pricingRepository,
        private readonly PricingConfigurationValidator $pricingValidator,
        private readonly string $appEnv,
        private readonly ?string $adminBaseUrl,
        private readonly string $contactRecipient,
        private readonly string $mailerDsn,
        /** @var list<string> */
        private readonly array $originAllowlist,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io      = new SymfonyStyle($input, $output);
        $isProd  = $this->appEnv === 'prod';
        $hasError = false;

        $io->title('Estimateur V1 — Preflight production');
        $io->writeln(\sprintf('Environnement : <info>%s</info>', $this->appEnv));

        // ── 1. Pricing active ─────────────────────────────────────────────
        $io->section('Grille tarifaire');
        $published = $this->pricingRepository->findPublished();

        if ($published === null) {
            $this->fail($io, 'pricing_active', 'Aucune grille tarifaire publiée — l\'estimateur retournera 500.', $isProd, $hasError);
        } else {
            $io->writeln(\sprintf('  ✔ Version publiée : <info>%s</info>', $published->version()));

            $def    = PricingCatalogDefinition::fromArray($published->version(), $published->currency(), $published->configuration());
            $errors = $this->pricingValidator->validate($def);
            if ($errors !== []) {
                foreach ($errors as $err) {
                    $this->fail($io, 'pricing_valid', $err, $isProd, $hasError);
                }
            } else {
                $io->writeln('  ✔ Configuration valide (toutes les clés obligatoires présentes)');
            }

            $all      = $this->pricingRepository->listAll();
            $pubCount = \count(\array_filter($all, fn ($c) => $c->status() === PricingConfigurationStatus::Published));
            if ($pubCount !== 1) {
                $this->fail($io, 'pricing_unique', \sprintf('%d grille(s) publiée(s) trouvée(s) — attendu : 1.', $pubCount), $isProd, $hasError);
            } else {
                $io->writeln('  ✔ Une seule grille publiée');
            }
        }

        // ── 2. APP_ADMIN_BASE_URL ─────────────────────────────────────────
        $io->section('APP_ADMIN_BASE_URL');
        if (!$this->adminBaseUrl) {
            $this->fail($io, 'admin_base_url_empty', 'APP_ADMIN_BASE_URL est vide — les liens dans les emails de notification seront incomplets.', $isProd, $hasError);
        } elseif ($isProd && !$this->isSecureAdminUrl($this->adminBaseUrl)) {
            $this->fail($io, 'admin_base_url_insecure', 'APP_ADMIN_BASE_URL doit être HTTPS et ne pas pointer vers localhost/127.0.0.1/::1 en production.', true, $hasError);
        } else {
            $io->writeln('  ✔ Présent (domaine non affiché)');
        }

        // ── 3. CONTACT_RECIPIENT ──────────────────────────────────────────
        $io->section('CONTACT_RECIPIENT');
        if ($this->contactRecipient === '') {
            $this->fail($io, 'contact_recipient', 'CONTACT_RECIPIENT est vide — les notifications email seront rejetées.', $isProd, $hasError);
        } else {
            $io->writeln('  ✔ Destinataire configuré');
        }

        // ── 4. MAILER_DSN ─────────────────────────────────────────────────
        $io->section('MAILER_DSN');
        if (\str_starts_with($this->mailerDsn, 'null://')) {
            if ($isProd) {
                $io->writeln('  ✘ [FAIL:mailer_dsn] MAILER_DSN = null:// en production — aucun email ne sera envoyé.');
                $hasError = true;
            } else {
                $io->writeln('  ℹ MAILER_DSN = null:// (dev/test — attendu)');
            }
        } else {
            $io->writeln('  ✔ Transport mail configuré (DSN non affiché)');
        }

        // ── 5. Origin allowlist ───────────────────────────────────────────
        $io->section('CONTACT_ORIGIN_ALLOWLIST');
        if ($this->originAllowlist === []) {
            $this->fail($io, 'origin_allowlist', 'CONTACT_ORIGIN_ALLOWLIST est vide — tous les appels CORS seront rejetés.', $isProd, $hasError);
        } else {
            $io->writeln(\sprintf('  ✔ %d origine(s) autorisée(s)', \count($this->originAllowlist)));
            if ($isProd) {
                $hasLocalhost = (bool) \array_filter(
                    $this->originAllowlist,
                    fn (string $o) => \str_contains($o, 'localhost') || \str_contains($o, '127.0.0.1'),
                );
                if ($hasLocalhost) {
                    $io->warning('  ⚠ L\'allowlist contient des origines localhost — vérifier en production.');
                }
            }
        }

        // ── 6. Indexabilité (WARN seulement) ─────────────────────────────
        $io->section('Indexabilité');
        $io->writeln('  ℹ Vérifier NUXT_PUBLIC_SITE_INDEXABLE=true au lancement public (Q-08).');
        $io->writeln('  ℹ Valeur actuelle : non vérifiée ici (variable Nuxt, pas Symfony).');

        // ── Résultat ──────────────────────────────────────────────────────
        $io->section('Résultat');
        if ($hasError) {
            $io->error('Preflight ÉCHOUÉ — corrigez les erreurs ci-dessus avant déploiement.');

            return Command::FAILURE;
        }

        $io->success('Preflight OK — configuration estimateur prête pour la production.');

        return Command::SUCCESS;
    }

    private function fail(SymfonyStyle $io, string $code, string $message, bool $isProd, bool &$hasError): void
    {
        if ($isProd) {
            $io->writeln(\sprintf('  ✘ [FAIL:%s] %s', $code, $message));
            $hasError = true;
        } else {
            $io->writeln(\sprintf('  ⚠ [WARN:%s] %s', $code, $message));
        }
    }

    private function isSecureAdminUrl(string $url): bool
    {
        $parsed = \parse_url($url);
        if ($parsed === false) {
            return false;
        }

        // Must be HTTPS.
        if (($parsed['scheme'] ?? '') !== 'https') {
            return false;
        }

        $host = \strtolower($parsed['host'] ?? '');

        // Must not be localhost or loopback addresses.
        // parse_url keeps brackets on IPv6: [::1].
        return !(\in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true)
            || \str_ends_with($host, '.localhost'));
    }
}
