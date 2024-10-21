<?php

namespace PrototypeIntegration\PrototypeIntegration\Tests\Unit\Processor;

use PrototypeIntegration\PrototypeIntegration\Processor\TypoLinkStringProcessor;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TypoLinkStringProcessorTest extends UnitTestCase
{
    protected $mockTypoLinkUrlResult = 'https://www.example.com/url';

    /**
     * @var TypoLinkStringProcessor
     */
    protected $typoLinkStringProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $eventDispatcherMock
            ->method('dispatch')
            ->willReturnArgument(0)
        ;

        $typoLinkCodecService = new TypoLinkCodecService($eventDispatcherMock);

        $contentObjectRendererMock = $this->createMock(ContentObjectRenderer::class);
        $contentObjectRendererMock
            ->method('typoLink_URL')
            ->willReturn($this->mockTypoLinkUrlResult)
        ;
        /** @var ContentObjectRenderer $contentObjectRendererMock */
        $this->typoLinkStringProcessor = new TypoLinkStringProcessor(
            $typoLinkCodecService,
            $contentObjectRendererMock
        );
    }

    /**
     * @test
     */
    public function simpleExternalUrl()
    {
        $resultingArray = $this->typoLinkStringProcessor->processTypoLinkString('https://www.example.com/asdf');

        self::assertEquals($this->mockTypoLinkUrlResult, $resultingArray['config']['uri']);
        self::assertEmpty($resultingArray['config']['target']);
        self::assertEmpty($resultingArray['config']['class']);
        self::assertEmpty($resultingArray['config']['title']);
    }

    /**
     * @test
     */
    public function linkWithBlankTarget()
    {
        $resultingArray = $this->typoLinkStringProcessor->processTypoLinkString('https://www.example.com/asdf _blank');

        self::assertEquals('_blank', $resultingArray['config']['target']);
    }
}
