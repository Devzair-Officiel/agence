<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Infrastructure\Logging;

use App\Admin\Domain\AdminEmail;
use App\Admin\Domain\AdminUser;
use App\EditorialMedia\Infrastructure\Logging\MediaAdminAuditLogger;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Vérifie que le canal `admin` reçoit des enregistrements structurés, sans PII,
 * sans nom d'origine, sans SHA-256 complet ni chemin de stockage.
 */
final class MediaAdminAuditLoggerTest extends TestCase
{
    private const EMAIL = 'admin@devzair.local';
    private const PASSWORD_HASH = '$argon2id$v=19$m=65536,t=4,p=1$fake$fakehashvalue';
    private const ASSET_ID = '01890000-0000-7000-8000-000000000001';
    private const SHA256 = 'aabbccddeeff00112233445566778899aabbccddeeff00112233445566778899';
    private const STORAGE_KEY_HINT = '01890000-0000-7000-8000-000000000001/original.jpg';

    private TestHandler $handler;
    private Logger $logger;
    private MediaAdminAuditLogger $audit;
    private AdminUser $admin;
    private Uuid $adminId;

    protected function setUp(): void
    {
        $this->handler = new TestHandler();
        $this->logger = new Logger('admin', [$this->handler]);
        $this->audit = new MediaAdminAuditLogger($this->logger);

        $this->adminId = Uuid::v7();
        $this->admin = $this->reifyAdmin($this->adminId, self::EMAIL);
    }

    public function testUploadedEmitsInfoWithNoPiiAndTruncatedSha(): void
    {
        $this->audit->uploaded(
            $this->admin,
            self::ASSET_ID,
            'image/jpeg',
            123_456,
            1024,
            768,
            self::SHA256,
        );

        self::assertTrue($this->handler->hasInfoThatContains('admin.media.uploaded'));

        $record = $this->firstRecord();
        self::assertSame(Level::Info, $record->level);
        self::assertSame('admin', $record->channel);
        self::assertSame($this->adminId->toRfc4122(), $record->context['admin_id']);
        self::assertSame(self::ASSET_ID, $record->context['asset_id']);
        self::assertSame('image/jpeg', $record->context['mime']);
        self::assertSame(123_456, $record->context['size_bytes']);
        self::assertSame(1024, $record->context['width']);
        self::assertSame(768, $record->context['height']);
        self::assertSame(substr(self::SHA256, 0, 12), $record->context['sha256_prefix']);
        self::assertSame(12, \strlen($record->context['sha256_prefix']));

        $this->assertLogsCarryNoPii();
        self::assertStringNotContainsString(self::SHA256, $this->dumpRecords());
        self::assertStringNotContainsString(self::STORAGE_KEY_HINT, $this->dumpRecords());
    }

    public function testUploadFailedEmitsWarningWithReasonOnly(): void
    {
        $this->audit->uploadFailed($this->admin, 'mime_unsupported');

        self::assertTrue($this->handler->hasWarningThatContains('admin.media.upload_failed'));

        $record = $this->firstRecord();
        self::assertSame(Level::Warning, $record->level);
        self::assertSame('admin', $record->channel);
        self::assertSame($this->adminId->toRfc4122(), $record->context['admin_id']);
        self::assertSame('mime_unsupported', $record->context['reason']);
        self::assertArrayNotHasKey('asset_id', $record->context);
        self::assertArrayNotHasKey('mime', $record->context);

        $this->assertLogsCarryNoPii();
    }

    public function testUploadRateLimitedEmitsWarningWithRetryAfter(): void
    {
        $this->audit->uploadRateLimited($this->admin, 42);

        self::assertTrue($this->handler->hasWarningThatContains('admin.media.upload_rate_limited'));

        $record = $this->firstRecord();
        self::assertSame(Level::Warning, $record->level);
        self::assertSame('admin', $record->channel);
        self::assertSame($this->adminId->toRfc4122(), $record->context['admin_id']);
        self::assertSame(42, $record->context['retry_after_seconds']);

        $this->assertLogsCarryNoPii();
    }

    public function testPreviewedEmitsInfoWithAssetAndMime(): void
    {
        $this->audit->previewed($this->admin, self::ASSET_ID, 'image/webp');

        self::assertTrue($this->handler->hasInfoThatContains('admin.media.previewed'));

        $record = $this->firstRecord();
        self::assertSame(Level::Info, $record->level);
        self::assertSame('admin', $record->channel);
        self::assertSame($this->adminId->toRfc4122(), $record->context['admin_id']);
        self::assertSame(self::ASSET_ID, $record->context['asset_id']);
        self::assertSame('image/webp', $record->context['mime']);
        self::assertArrayNotHasKey('storage_key', $record->context);
        self::assertArrayNotHasKey('size_bytes', $record->context);

        $this->assertLogsCarryNoPii();
    }

    private function firstRecord(): \Monolog\LogRecord
    {
        $records = $this->handler->getRecords();
        self::assertNotEmpty($records, 'Aucun enregistrement Monolog capturé.');

        return $records[0];
    }

    private function dumpRecords(): string
    {
        return json_encode(
            array_map(
                static fn (\Monolog\LogRecord $r): array => [
                    'channel' => $r->channel,
                    'level' => $r->level->name,
                    'message' => $r->message,
                    'context' => $r->context,
                ],
                $this->handler->getRecords(),
            ),
            \JSON_THROW_ON_ERROR,
        );
    }

    private function assertLogsCarryNoPii(): void
    {
        $dump = $this->dumpRecords();
        self::assertStringNotContainsString(self::EMAIL, $dump, 'L\'email ne doit jamais apparaître dans les logs admin.');
        self::assertStringNotContainsString('Admin Local', $dump, 'Le display name ne doit pas apparaître dans les logs admin.');
        self::assertStringNotContainsString(self::PASSWORD_HASH, $dump, 'Le hash du mot de passe ne doit jamais fuir.');
    }

    private function reifyAdmin(Uuid $id, string $email): AdminUser
    {
        return AdminUser::create(
            $id,
            AdminEmail::fromString($email),
            'Admin Local',
            self::PASSWORD_HASH,
            new \DateTimeImmutable('2026-08-08T12:00:00+00:00'),
        );
    }
}
