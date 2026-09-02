<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Presentation\Console;

use App\Estimator\Application\Pricing\PricingConfigurationValidator;
use App\Estimator\Domain\Pricing\PricingConfiguration;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use App\Estimator\Domain\Pricing\PricingConfigurationStatus;
use App\Estimator\Presentation\Console\EstimatorPreflightCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Teste les gates de validation de EstimatorPreflightCommand.
 *
 * Ces tests sont des tests unitaires purs : ils instancient la commande
 * directement avec des doubles de test, sans démarrer le container Symfony.
 *
 * PricingConfigurationValidator est final → on ne peut pas la mocker.
 * On utilise la vraie implémentation avec une configuration vide qui
 * retourne des erreurs de validation, mais cela n'affecte pas les gates
 * testées ici (MAILER_DSN, APP_ADMIN_BASE_URL). En dev/test les erreurs
 * pricing sont WARN, pas FAIL, et n'influencent pas le code de sortie.
 */
final class EstimatorPreflightCommandTest extends TestCase
{
    private PricingConfigurationRepositoryInterface&MockObject $pricingRepo;
    private PricingConfigurationValidator $validator;

    protected function setUp(): void
    {
        $this->pricingRepo = $this->createMock(PricingConfigurationRepositoryInterface::class);
        $this->validator   = new PricingConfigurationValidator();
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function makeConfig(): PricingConfiguration&MockObject
    {
        $config = $this->createMock(PricingConfiguration::class);
        $config->method('version')->willReturn('2026-v1');
        $config->method('currency')->willReturn('EUR');
        $config->method('configuration')->willReturn(self::validConfigArray());
        $config->method('status')->willReturn(PricingConfigurationStatus::Published);

        return $config;
    }

    /** @return array<string, mixed> */
    private static function validConfigArray(): array
    {
        return [
            'base_amounts' => [
                'vitrinesite' => ['min' => 90_000,  'max' => 130_000],
                'ecommerce'   => ['min' => 170_000, 'max' => 250_000],
                'businessapp' => ['min' => 250_000, 'max' => 400_000],
                'refonte'     => ['min' => 70_000,  'max' => 130_000],
            ],
            'scale_percents' => [
                'small'   => ['min' => 100, 'max' => 100],
                'medium'  => ['min' => 115, 'max' => 120],
                'large'   => ['min' => 130, 'max' => 145],
                'unknown' => ['min' => 100, 'max' => 100],
            ],
            'feature_complexity' => [
                'contact_form'          => 'light',
                'multilingual'          => 'light',
                'content_management'    => 'light',
                'booking_form'          => 'medium',
                'payment'               => 'medium',
                'shipping'              => 'medium',
                'customer_accounts'     => 'medium',
                'notifications'         => 'medium',
                'import_export'         => 'medium',
                'stock_management'      => 'medium',
                'promotions'            => 'medium',
                'authentication'        => 'advanced',
                'roles_and_permissions' => 'advanced',
                'document_generation'   => 'advanced',
                'api_integration'       => 'advanced',
            ],
            'complexity_amounts' => [
                'light'    => ['min' => 10_000,  'max' => 35_000],
                'medium'   => ['min' => 35_000,  'max' => 80_000],
                'advanced' => ['min' => 70_000,  'max' => 180_000],
            ],
            'content_amounts' => [
                'visual_identity_needed'  => ['min' => 50_000, 'max' => 100_000],
                'visual_identity_partial' => ['min' => 25_000, 'max' => 50_000],
                'content_to_write'        => ['min' => 40_000, 'max' => 90_000],
                'content_with_help'       => ['min' => 15_000, 'max' => 40_000],
                'photography_needed'      => ['min' => 25_000, 'max' => 60_000],
            ],
            'visibility_amounts' => [
                'seo_basic'          => null,
                'seo_advanced'       => ['min' => 30_000, 'max' => 80_000],
                'local_visibility'   => ['min' => 20_000, 'max' => 50_000],
                'editorial_strategy' => ['min' => 30_000, 'max' => 70_000],
            ],
            'recurring_amounts' => [
                'ESSENTIAL_MAINTENANCE' => ['min' => 4_900,  'max' => 6_900],
                'MAINTENANCE_FOLLOWUP'  => ['min' => 8_900,  'max' => 12_900],
                'ACCOMPANIMENT'         => ['min' => 14_900, 'max' => 21_900],
                'REGULAR_EVOLUTION'     => ['min' => 24_900, 'max' => 39_900],
                'CONTINUOUS_SEO'        => ['min' => 30_000, 'max' => 60_000],
            ],
        ];
    }

    /**
     * Construit la commande avec des valeurs ok par défaut (pricing publiée,
     * destinataire et allowlist non vides), les arguments testés étant fournis
     * explicitement.
     *
     * @param list<string> $originAllowlist
     */
    private function buildCommand(
        string $appEnv,
        ?string $adminBaseUrl,
        string $mailerDsn,
        string $contactRecipient = 'contact@devzair.test',
        array $originAllowlist = ['http://localhost:3001'],
    ): CommandTester {
        $config = $this->makeConfig();
        $this->pricingRepo->method('findPublished')->willReturn($config);
        $this->pricingRepo->method('listAll')->willReturn([$config]);

        $command = new EstimatorPreflightCommand(
            $this->pricingRepo,
            $this->validator,
            $appEnv,
            $adminBaseUrl,
            $contactRecipient,
            $mailerDsn,
            $originAllowlist,
        );

        return new CommandTester($command);
    }

    // ── MAILER_DSN ────────────────────────────────────────────────────────

    public function test_prod_null_mailer_dsn_is_fail(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'prod',
            adminBaseUrl: 'https://admin.devzair.fr',
            mailerDsn: 'null://null',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('FAIL:mailer_dsn', $tester->getDisplay());
    }

    public function test_dev_null_mailer_dsn_is_info_not_fail(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'dev',
            adminBaseUrl: 'http://localhost:8000',
            mailerDsn: 'null://null',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringNotContainsString('FAIL:mailer_dsn', $tester->getDisplay());
        self::assertStringContainsString('null://', $tester->getDisplay());
    }

    public function test_prod_real_mailer_dsn_passes(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'prod',
            adminBaseUrl: 'https://admin.devzair.fr',
            mailerDsn: 'smtp://user:pass@smtp.example.com:587',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Transport mail configuré', $tester->getDisplay());
    }

    // ── APP_ADMIN_BASE_URL ────────────────────────────────────────────────

    public function test_prod_empty_admin_base_url_is_fail(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'prod',
            adminBaseUrl: null,
            mailerDsn: 'smtp://user:pass@smtp.example.com:587',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('FAIL:admin_base_url_empty', $tester->getDisplay());
    }

    public function test_prod_localhost_admin_base_url_is_fail(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'prod',
            adminBaseUrl: 'https://localhost/admin',
            mailerDsn: 'smtp://user:pass@smtp.example.com:587',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('FAIL:admin_base_url_insecure', $tester->getDisplay());
    }

    public function test_prod_127_0_0_1_admin_base_url_is_fail(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'prod',
            adminBaseUrl: 'https://127.0.0.1/admin',
            mailerDsn: 'smtp://user:pass@smtp.example.com:587',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('FAIL:admin_base_url_insecure', $tester->getDisplay());
    }

    public function test_prod_http_admin_base_url_is_fail(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'prod',
            adminBaseUrl: 'http://admin.devzair.fr',
            mailerDsn: 'smtp://user:pass@smtp.example.com:587',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('FAIL:admin_base_url_insecure', $tester->getDisplay());
    }

    public function test_prod_https_admin_base_url_passes(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'prod',
            adminBaseUrl: 'https://admin.devzair.fr',
            mailerDsn: 'smtp://user:pass@smtp.example.com:587',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Présent (domaine non affiché)', $tester->getDisplay());
    }

    public function test_dev_localhost_admin_base_url_is_warn_not_fail(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'dev',
            adminBaseUrl: 'http://localhost:8000',
            mailerDsn: 'null://null',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringNotContainsString('FAIL:admin_base_url', $tester->getDisplay());
    }

    public function test_prod_ipv6_loopback_admin_base_url_is_fail(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'prod',
            adminBaseUrl: 'https://[::1]/admin',
            mailerDsn: 'smtp://user:pass@smtp.example.com:587',
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('FAIL:admin_base_url_insecure', $tester->getDisplay());
    }

    // ── Résultat global ───────────────────────────────────────────────────

    public function test_prod_all_ok_exits_success(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'prod',
            adminBaseUrl: 'https://admin.devzair.fr',
            mailerDsn: 'smtp://user:pass@smtp.example.com:587',
            contactRecipient: 'ops@devzair.fr',
            originAllowlist: ['https://devzair.fr'],
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Preflight OK', $tester->getDisplay());
    }

    public function test_dev_environment_exits_success_with_dev_defaults(): void
    {
        $tester = $this->buildCommand(
            appEnv: 'dev',
            adminBaseUrl: 'http://localhost:8000',
            mailerDsn: 'null://null',
            contactRecipient: '',
            originAllowlist: [],
        );

        $exitCode = $tester->execute([]);
        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringNotContainsString('FAIL:', $tester->getDisplay());
    }
}
