<?php

namespace PrototypeIntegration\PrototypeIntegration\Tests\Unit\Processor;

use PHPUnit\Framework\TestCase;
use PrototypeIntegration\PrototypeIntegration\Processor\TypoLinkStringProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

class TypoLinkStringProcessorTest extends TestCase
{
    protected $mockTypoLinkUrlResult = 'https://www.example.com/url';

    /**
     * @var TypoLinkStringProcessor
     */
    protected $typoLinkStringProcessor;

    protected function setUp()
    {
        parent::setUp();

        $typoLinkCodeService = new TypoLinkCodecService();

        $contentObjectRendererMock = $this->createMock(ContentObjectRenderer::class);
        $contentObjectRendererMock
            ->method('typoLink_URL')
            ->willReturn($this->mockTypoLinkUrlResult)
        ;
        /** @var ContentObjectRenderer $contentObjectRendererMock */

        $this->typoLinkStringProcessor = new TypoLinkStringProcessor(
            $typoLinkCodeService,
            $contentObjectRendererMock
        );
    }

    /**
     * @test
     */
    public function simpleExternalUrl()
    {
        $resultingArray = $this->typoLinkStringProcessor->processTypoLinkString('https://www.example.com/asdf');

        $this->assertEquals($this->mockTypoLinkUrlResult, $resultingArray['config']['uri']);
        $this->assertEmpty($resultingArray['config']['target']);
        $this->assertEmpty($resultingArray['config']['class']);
        $this->assertEmpty($resultingArray['config']['title']);
    }

    /**
     * @test
     */
    public function linkWithBlankTarget()
    {
        $resultingArray = $this->typoLinkStringProcessor->processTypoLinkString('https://www.example.com/asdf _blank');

        $this->assertEquals('_blank', $resultingArray['config']['target']);
    }
}
