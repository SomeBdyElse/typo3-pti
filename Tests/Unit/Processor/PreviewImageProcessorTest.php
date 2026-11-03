<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Tests\Unit\Processor;

use PrototypeIntegration\PrototypeIntegration\Processor\PreviewImageProcessor;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\FileType;
use TYPO3\CMS\Core\Resource\Index\MetaDataRepository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PreviewImageProcessorTest extends UnitTestCase
{
    public function testImageCanBeUsedAsItsOwnPreview(): void
    {
        $file = $this->createMock(File::class);
        $file->method('hasProperty')->willReturn(false);
        $file->method('getType')->willReturn(FileType::IMAGE->value);
        $processor = new PreviewImageProcessor(
            $this->createMock(MetaDataRepository::class),
            $this->createMock(FileRepository::class),
        );
        self::assertSame($file, $processor->getPreviewImage($file));
    }
}
