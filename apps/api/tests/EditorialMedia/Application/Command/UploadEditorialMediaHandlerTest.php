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
use App\Tests\EditorialMedia\Support\FakeImageVariantProcessor;
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

    private function makeHandler(
        FakeMediaStorage $storage,
        InMemoryMediaAssetRepository $repository,
        EntityManagerInterface $em,
        FakeImageProcessor $processor = new FakeImageProcessor(),
        FakeImageVariantProcessor $variantProcessor = new FakeImageVariantProcessor(),
        string $mime = 'image/jpeg',
    ): UploadEditorialMediaHandler {
        return new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector($mime),
            new MediaUploadPolicy(),
            $processor,
            $variantProcessor,
            $storage,
            $storage, // même objet implémente les deux interfaces
            $repository,
            $em,
            new FixedClock('2026-09-13T10:00:00+00:00'),
        );
    }

    public function testHappyPathPersistsAssetWithThreeFilesAndVariants(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage    = new FakeMediaStorage();
        $handler    = $this->makeHandler($storage, $repository, $this->entityManagerExpectingFlush());

        $result = $handler(new UploadEditorialMedia($this->sourcePath, 'photo.jpg'));

        // Un seul asset en DB
        self::assertSame(1, $repository->count());

        // Trois fichiers en storage : original + card + hero
        self::assertSame(1, $storage->moveIntoCallCount);
        self::assertSame(2, $storage->moveVariantIntoCallCount);
        self::assertCount(3, $storage->files);
        self::assertSame(0, $storage->deleteCallCount);
        self::assertSame(0, $storage->deleteVariantCallCount);

        // Les variants sont attachés au MediaAsset
        self::assertTrue($result->asset->hasVariants());
        self::assertNotNull($result->asset->cardWidth());
        self::assertNotNull($result->asset->heroWidth());
    }

    public function testStorageKeysFollowExpectedPattern(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage    = new FakeMediaStorage();
        $handler    = $this->makeHandler($storage, $repository, $this->entityManagerExpectingFlush());

        $result = $handler(new UploadEditorialMedia($this->sourcePath, 'schema.png'));
        $uuid   = $result->asset->id()->toRfc4122();

        self::assertArrayHasKey("{$uuid}/original.jpg", $storage->files,  'original absent');
        self::assertArrayHasKey("{$uuid}/card.webp",    $storage->files, 'card absent');
        self::assertArrayHasKey("{$uuid}/hero.webp",    $storage->files, 'hero absent');
    }

    public function testVariantDimensionsArePersistedFromProcessor(): void
    {
        $repository      = new InMemoryMediaAssetRepository();
        $storage         = new FakeMediaStorage();
        $variantProc     = new FakeImageVariantProcessor(cardWidth: 592, cardHeight: 333, heroWidth: 592, heroHeight: 333);
        $handler         = $this->makeHandler($storage, $repository, $this->entityManagerExpectingFlush(), variantProcessor: $variantProc);

        $result = $handler(new UploadEditorialMedia($this->sourcePath, 'small.jpg'));

        self::assertSame(592, $result->asset->cardWidth());
        self::assertSame(333, $result->asset->cardHeight());
        self::assertSame(592, $result->asset->heroWidth());
        self::assertSame(333, $result->asset->heroHeight());
    }

    public function testVariantRatioIs16By9(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage    = new FakeMediaStorage();
        $handler    = $this->makeHandler($storage, $repository, $this->entityManagerExpectingFlush());

        $result = $handler(new UploadEditorialMedia($this->sourcePath, 'photo.jpg'));

        $cw = $result->asset->cardWidth();
        $ch = $result->asset->cardHeight();
        $hw = $result->asset->heroWidth();
        $hh = $result->asset->heroHeight();
        self::assertNotNull($cw); self::assertNotNull($ch);
        self::assertNotNull($hw); self::assertNotNull($hh);
        self::assertSame(0, $cw * 9 - $ch * 16, 'card n\'est pas exactement 16:9');
        self::assertSame(0, $hw * 9 - $hh * 16, 'hero n\'est pas exactement 16:9');
    }

    public function testRejectsMimeBeforeCallingProcessors(): void
    {
        $repository     = new InMemoryMediaAssetRepository();
        $storage        = new FakeMediaStorage();
        $processor      = new FakeImageProcessor();
        $variantProc    = new FakeImageVariantProcessor();
        $handler        = new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/gif'),
            new MediaUploadPolicy(),
            $processor,
            $variantProc,
            $storage, $storage,
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-09-13T10:00:00+00:00'),
        );

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'anim.gif'));
            self::fail('UnsupportedMediaTypeException attendue.');
        } catch (UnsupportedMediaTypeException) {
            self::assertSame(0, $processor->normalizeCallCount);
            self::assertSame(0, $variantProc->generateVariantsCallCount);
            self::assertSame(0, $storage->moveIntoCallCount);
            self::assertCount(0, $storage->files);
        }
    }

    public function testVariantFailureRollsBackAllFiles(): void
    {
        $repository  = new InMemoryMediaAssetRepository();
        $storage     = new FakeMediaStorage();
        $variantProc = new FakeImageVariantProcessor();
        $variantProc->throwable = new InvalidImageException('GD error.');

        $handler = $this->makeHandler($storage, $repository, $this->entityManagerExpectingNoFlush(), variantProcessor: $variantProc);

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'bad.jpg'));
            self::fail('InvalidImageException attendue.');
        } catch (InvalidImageException) {
            // Aucun fichier en storage : l'original n'a pas encore été déplacé
            // (la génération des variants précède le premier moveInto)
            self::assertCount(0, $storage->files);
            self::assertSame(0, $storage->moveIntoCallCount);
            self::assertSame(0, $repository->count());
        }
    }

    public function testMoveCardFailureRollsBackOriginalAndCleansTempFiles(): void
    {
        $repository  = new InMemoryMediaAssetRepository();
        $storage     = new FakeMediaStorage(moveVariantFails: true);
        $handler     = $this->makeHandler($storage, $repository, $this->entityManagerExpectingNoFlush());

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'test.jpg'));
            self::fail('MediaStorageException attendue.');
        } catch (MediaStorageException) {
            // Original avait été déplacé, puis supprimé en compensation
            self::assertSame(1, $storage->moveIntoCallCount);
            self::assertSame(1, $storage->deleteCallCount);
            self::assertCount(0, $storage->files);
            self::assertSame(0, $repository->count());
        }
    }

    public function testFlushFailureDeletesAllThreeFiles(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage    = new FakeMediaStorage();
        $em         = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->expects(self::once())->method('flush')->willThrowException(new \RuntimeException('Deadlock.'));

        $handler = $this->makeHandler($storage, $repository, $em);

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'flush-fail.jpg'));
            self::fail('RuntimeException attendue.');
        } catch (\RuntimeException $e) {
            self::assertSame('Deadlock.', $e->getMessage());
            // Les 3 fichiers doivent avoir été supprimés
            self::assertSame(1, $storage->deleteCallCount);
            self::assertSame(2, $storage->deleteVariantCallCount);
            self::assertCount(0, $storage->files);
        }
    }

    public function testRejectsDimensionsAfterNormalizationWithoutStoring(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage    = new FakeMediaStorage();
        $processor  = new FakeImageProcessor(
            forcedDimensions: MediaDimensions::of(MediaUploadPolicy::MAX_WIDTH + 1, 100),
        );
        $handler = $this->makeHandler($storage, $repository, $this->entityManagerExpectingNoFlush(), $processor);

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'wide.jpg'));
            self::fail('ImageDimensionsExceededException attendue.');
        } catch (ImageDimensionsExceededException) {
            self::assertSame(0, $storage->moveIntoCallCount);
            self::assertSame(0, $repository->count());
        }
    }

    public function testCompensationSwallowsSecondaryStorageError(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $storage    = new FakeMediaStorage(deleteFails: true);
        $em         = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->expects(self::once())->method('flush')->willThrowException(new \RuntimeException('Erreur flush.'));

        $handler = $this->makeHandler($storage, $repository, $em);

        try {
            $handler(new UploadEditorialMedia($this->sourcePath, 'sw.jpg'));
            self::fail('RuntimeException originelle attendue.');
        } catch (\RuntimeException $e) {
            self::assertSame('Erreur flush.', $e->getMessage());
        }
    }

    public function testAssetCreatedAtUsesClockNow(): void
    {
        $repository = new InMemoryMediaAssetRepository();
        $result     = (new UploadEditorialMediaHandler(
            new FakeMimeTypeDetector('image/webp'),
            new MediaUploadPolicy(),
            new FakeImageProcessor(forcedType: MediaType::WebP, forcedSha256: Sha256::fromString(str_repeat('c', 64))),
            new FakeImageVariantProcessor(),
            new FakeMediaStorage(), new FakeMediaStorage(),
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-09-13T09:30:00+00:00'),
        ))(new UploadEditorialMedia($this->sourcePath, 'banner.webp'));

        self::assertEquals(
            new \DateTimeImmutable('2026-09-13T09:30:00+00:00'),
            $result->asset->createdAt(),
        );
        self::assertSame(MediaType::WebP, $result->asset->mimeType());
    }
}
