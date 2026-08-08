<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Infrastructure\Storage;

use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\StorageKey;
use App\EditorialMedia\Infrastructure\Storage\LocalMediaStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;

final class LocalMediaStorageTest extends TestCase
{
    private string $root;
    private Filesystem $fs;

    protected function setUp(): void
    {
        $this->fs = new Filesystem();
        $this->root = sys_get_temp_dir() . '/editorial-media-test-' . uniqid();
        $this->fs->mkdir($this->root, 0750);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->root)) {
            $this->fs->remove($this->root);
        }
    }

    public function testMoveIntoWritesFileAtCanonicalPath(): void
    {
        $storage = new LocalMediaStorage($this->root, $this->fs);
        $tmp = $this->writeTempFile('payload');
        $id = Uuid::v7();
        $key = StorageKey::forNewAsset($id, MediaType::Jpeg);

        $storage->moveInto($tmp, $key);

        $expected = $this->root . '/' . $id->toRfc4122() . '/original.jpg';
        self::assertFileExists($expected);
        self::assertSame('payload', file_get_contents($expected));
        self::assertFileDoesNotExist($tmp);
    }

    public function testMoveIntoAppliesRestrictiveFileMode(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Modes POSIX non applicables sous Windows.');
        }

        $storage = new LocalMediaStorage($this->root, $this->fs);
        $tmp = $this->writeTempFile('payload');
        $id = Uuid::v7();
        $key = StorageKey::forNewAsset($id, MediaType::Png);

        $storage->moveInto($tmp, $key);

        $mode = fileperms($this->root . '/' . $id->toRfc4122() . '/original.png') & 0777;
        self::assertSame(0640, $mode);
    }

    public function testExistsAndOpenReadStreamRoundTrip(): void
    {
        $storage = new LocalMediaStorage($this->root, $this->fs);
        $id = Uuid::v7();
        $key = StorageKey::forNewAsset($id, MediaType::WebP);

        self::assertFalse($storage->exists($key));

        $tmp = $this->writeTempFile('binaire-webp');
        $storage->moveInto($tmp, $key);
        self::assertTrue($storage->exists($key));

        $stream = $storage->openReadStream($key);
        self::assertIsResource($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        self::assertSame('binaire-webp', $contents);
    }

    public function testDeleteRemovesFileAndEmptyParentDirectory(): void
    {
        $storage = new LocalMediaStorage($this->root, $this->fs);
        $id = Uuid::v7();
        $key = StorageKey::forNewAsset($id, MediaType::Jpeg);
        $storage->moveInto($this->writeTempFile('x'), $key);
        $parent = $this->root . '/' . $id->toRfc4122();
        self::assertDirectoryExists($parent);

        $storage->delete($key);

        self::assertFalse($storage->exists($key));
        self::assertDirectoryDoesNotExist($parent);
    }

    public function testDeleteIsIdempotentWhenFileAlreadyGone(): void
    {
        $storage = new LocalMediaStorage($this->root, $this->fs);
        $id = Uuid::v7();
        $key = StorageKey::forNewAsset($id, MediaType::Jpeg);

        $storage->delete($key);
        $this->addToAssertionCount(1);
    }

    public function testOpenReadStreamOnMissingFileFails(): void
    {
        $storage = new LocalMediaStorage($this->root, $this->fs);
        $id = Uuid::v7();
        $key = StorageKey::forNewAsset($id, MediaType::Jpeg);

        $this->expectException(MediaStorageException::class);

        $storage->openReadStream($key);
    }

    private function writeTempFile(string $contents): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'lms-source-');
        self::assertNotFalse($tmp);
        file_put_contents($tmp, $contents);

        return $tmp;
    }
}
