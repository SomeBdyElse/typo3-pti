<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Tests\Unit\Processor;

use PHPUnit\Framework\Attributes\DataProvider;
use PrototypeIntegration\PrototypeIntegration\Processor\MediaProcessor;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileType;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class MediaProcessorTest extends UnitTestCase
{
    public static function mediaTypes(): array
    {
        return [
            'image' => [FileType::IMAGE->value, 'renderImageElement'],
            'video' => [FileType::VIDEO->value, 'renderVideoElement'],
        ];
    }

    #[DataProvider('mediaTypes')]
    public function testDispatchesMediaToTheMatchingRenderer(int $type, string $method): void
    {
        $file = $this->createMock(File::class);
        $file->method('getType')->willReturn($type);
        $processor = $this->getMockBuilder(MediaProcessor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['renderImageElement', 'renderVideoElement'])
            ->getMock();
        $processor->expects(self::once())->method($method)->willReturn(['type' => $type]);
        self::assertSame(['type' => $type], $processor->renderMediaElement($file));
    }

    public function testUnsupportedFileIsIgnored(): void
    {
        $processor = $this->getMockBuilder(MediaProcessor::class)
            ->disableOriginalConstructor()->onlyMethods([])->getMock();
        self::assertNull($processor->renderMediaElement($this->createMock(FileInterface::class)));
    }
}
