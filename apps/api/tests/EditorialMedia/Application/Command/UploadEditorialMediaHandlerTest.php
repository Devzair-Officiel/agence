<?php

declare(strict_types=1);

namespace App\Tests\EditorialMedia\Application\Command;

use App\EditorialMedia\Application\Command\UploadEditorialMedia;
use App\EditorialMedia\Application\Command\UploadEditorialMediaHandler;
use App\EditorialMedia\Application\Exception\ImageDimensionsExceededException;
use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Exception\MediaTooLargeException;
use App\EditorialMedia\Application\Exception\UnsupportedMediaTypeException;
use App\EditorialMedia\Application\Policy\MediaUploadPolicy;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\EditorialMedia\Support\FakeImageProcessor;
use App\Tests\EditorialMedia\Support\FakeMediaStorage;
use App\Tests\EditorialMedia\Support\FakeMimeTypeDetector;
use App\Tests\EditorialMedia\Support\InMemoryMediaAssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class UploadEditorialMediaHandlerTest extends TestCase
{
    use EntityManagerStub;

    private string $sourcePath;

    protected function setUp(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upload-source-');
        self::assertNotFalse($tmp);
        file_put_contents($tmp, str_repeat('x', 1024));
        $this->sourcePath = $tmp;
    }

    protected function tearDown(): void
    {
        if (is_file($this->sourcePath)) {
            @unlink($this->sourcePath);
        }
    }

    public function testHappyPathPersistsAssetAndStoresFileOnce(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $processor = new FakeImageProcessor();
        $handler = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/jpeg'),
            new MediaUploadPolicy(),
            $processor,
            $storage,
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-08T12:00:00+00:00'),
        );

        $result = $handler(new UploadEditorialMedia($this->sourcePath, 'photo.JPG'));

        self::assertSame(MediaType::Jpeg, $result->asset->mimeType());
        self::assertSame('photo.JPG', $result->asset->originalFilename());
        self::assertSame(1, $processor->normalizeCallCount);
        self::assertSame(1, $storage->moveIntoCallCount);
        self::assertCount(1, $storage->files);
        self::assertSame(0, $storage->deleteCallCount);
        self::assertSame(1, $repository->count());
    }

    public function testStorageKeyMatchesGeneratedUuidAndExtension(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $handler = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/png'),
            new MediaUploadPolicy(),
            new FakeImageProcessor(),
            $storage,
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-08T12:00:00+00:00'),
        );

        $result = $handler(new UploadEditorialMedia($this->sourcePath, 'schema.png'));

        $expectedKey = $result->asset->id()->toRfc4122() . '/original.png';
        self::assertArrayHasKey($expectedKey, $storage->files);
        self::assertSame($expectedKey, $result->asset->storageKey()->toString());
    }

    public function testRejectsMimeBeforeCallingProcessor(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $processor = new FakeImageProcessor();
        $handler = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/gif'),
            new MediaUploadPolicy(),
            $processor,
            $storage,
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-08T12:00:00+00:00'),
        );

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'anim.gif'));
            self::fail('Attendu : UnsupportedMediaTypeException.');
        } catch (UnsupportedMediaTypeException) {
            self::assertSame(0, $processor->normalizeCallCount);
            self::assertSame(0, $storage->moveIntoCallCount);
            self::assertSame(0, $repository->count());
        }
    }

    public function testRejectsRawSizeBeforeCallingProcessor(): void
    {
        $huge = tempnam(sys_get_temp_dir(), 'huge-');
        self::assertNotFalse($huge);
        try {
            file_put_contents($huge, str_repeat('x', MediaUploadPolicy::MAX_SIZE_BYTES + 1));

            $repository = new InMemoryMediaAssetRepository();
            $storage = new FakeMediaStorage();
            $processor = new FakeImageProcessor();
            $handler = new UploadEditorialMediaHandler(
                new FakeMimeTypeDetector('image/jpeg'),
                new MediaUploadPolicy(),
                $processor,
                $storage,
                $repository,
                $this->entityManagerExpectingNoFlush(),
                new FixedClock('2026-08-08T12:00:00+00:00'),
            );

            $this->expectException(MediaTooLargeException::class);

            try {
                $handler(new UploadEditorialMedia($huge, 'lourd.jpg'));
            } finally {
                self::assertSame(0, $processor->normalizeCallCount);
                self::assertSame(0, $storage->moveIntoCallCount);
            }
        } finally {
            @unlink($huge);
        }
    }

    public function testRejectsDimensionsAfterNormalizationWithoutStoring(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $processor = new FakeImageProcessor(
            forcedDimensions: MediaDimensions::of(
                MediaUploadPolicy::MAX_WIDTH + 1,
                100,
            ),
        );
        $handler = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/jpeg'),
            new MediaUploadPolicy(),
            $processor,
            $storage,
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-08T12:00:00+00:00'),
        );

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'wide.jpg'));
            self::fail('Attendu : ImageDimensionsExceededException.');
        } catch (ImageDimensionsExceededException) {
            self::assertSame(1, $processor->normalizeCallCount);
            self::assertSame(0, $storage->moveIntoCallCount);
            self::assertSame(0, $repository->count());
        }
    }

    public function testProcessorFailurePropagatesWithoutStorageWrite(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $processor = new FakeImageProcessor(
            throwable: new InvalidImageException('Décodage impossible.'),
        );
        $handler = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/jpeg'),
            new MediaUploadPolicy(),
            $processor,
            $storage,
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-08T12:00:00+00:00'),
        );

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'faux.jpg'));
            self::fail('Attendu : InvalidImageException.');
        } catch (InvalidImageException) {
            self::assertSame(0, $storage->moveIntoCallCount);
            self::assertSame(0, $repository->count());
        }
    }

    public function testStorageFailureCleansUpTemporaryFile(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage(moveFails: true);
        $processor = new FakeImageProcessor();
        $handler = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/jpeg'),
            new MediaUploadPolicy(),
            $processor,
            $storage,
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-08T12:00:00+00:00'),
        );

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'test.jpg'));
            self::fail('Attendu : MediaStorageException.');
        } catch (MediaStorageException) {
            self::assertSame(0, $repository->count());
            self::assertNotNull($processor->lastTemporaryPath);
            self::assertFileDoesNotExist(
                $processor->lastTemporaryPath,
                'Le fichier temporaire doit être nettoyé après un échec de storage.',
            );
        }
    }

    public function testFlushFailureTriggersCompensationDelete(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage();
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->expects(self::once())
            ->method('flush')
            ->willThrowException(new \RuntimeException('Deadlock Doctrine.'));

        $handler = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/jpeg'),
            new MediaUploadPolicy(),
            new FakeImageProcessor(),
            $storage,
            $repository,
            $em,
            new FixedClock('2026-08-08T12:00:00+00:00'),
        );

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'flush-fail.jpg'));
            self::fail('Attendu : RuntimeException.');
        } catch (\RuntimeException $e) {
            self::assertSame('Deadlock Doctrine.', $e->getMessage());
            self::assertSame(1, $storage->moveIntoCallCount);
            self::assertSame(1, $storage->deleteCallCount);
            self::assertCount(0, $storage->files);
        }
    }

    public function testCompensationSwallowsSecondaryStorageError(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage = new FakeMediaStorage(deleteFails: true);
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->expects(self::once())
            ->method('flush')
            ->willThrowException(new \RuntimeException('Erreur métier flush.'));

        $handler = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/jpeg'),
            new MediaUploadPolicy(),
            new FakeImageProcessor(),
            $storage,
            $repository,
            $em,
            new FixedClock('2026-08-08T12:00:00+00:00'),
        );

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'sw.jpg'));
            self::fail('Attendu : RuntimeException originelle propagée.');
        } catch (\RuntimeException $e) {
            self::assertSame('Erreur métier flush.', $e->getMessage());
            self::assertSame(1, $storage->deleteCallCount);
        }
    }

    public function testAssetCreatedAtUsesClockNow(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $handler = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/webp'),
            new MediaUploadPolicy(),
            new FakeImageProcessor(
                forcedType: MediaType::WebP,
                forcedSha256: Sha256::fromString(str_repeat('c', 64)),
            ),
            new FakeMediaStorage(),
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-09-01T09:30:00+00:00'),
        );

        $result = $handler(new UploadEditorialMedia($this->sourcePath, 'baniere.webp'));

        self::assertEquals(
            new \DateTimeImmutable('2026-09-01T09:30:00+00:00'),
            $result->asset->createdAt(),
        );
        self::assertSame(MediaType::WebP, $result->asset->mimeType());
        self::assertSame(str_repeat('c', 64), $result->asset->sha256()->toString());
    }
}
