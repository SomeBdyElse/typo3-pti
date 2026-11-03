<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Tests\Unit\Processor;

use PrototypeIntegration\PrototypeIntegration\Processor\CompoundProcessor;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CompoundProcessorTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function testNestedContentObjectReceivesTheActiveRequest(): void
    {
        $request = new ServerRequest();
        $parent = $this->createMock(ContentObjectRenderer::class);
        $parent->method('getRequest')->willReturn($request);
        $parent->method('getCurrentTable')->willReturn('pages');
        $child = $this->createMock(ContentObjectRenderer::class);
        $hasRequest = false;
        $child->expects(self::once())->method('setRequest')->with($request)
            ->willReturnCallback(static function () use (&$hasRequest): void { $hasRequest = true; });
        $child->expects(self::once())->method('start')
            ->willReturnCallback(static function () use (&$hasRequest): void { self::assertTrue($hasRequest); });
        $child->method('cObjGetSingle')->willReturn('nested content');
        GeneralUtility::addInstance(ContentObjectRenderer::class, $child);
        $processor = new CompoundProcessor(new TypoScriptService());
        $processor->injectContentObjectRenderer($parent);
        self::assertSame(['content' => 'nested content'], $processor->process([], [
            'content' => ['_typoScriptNodeValue' => 'TEXT', 'value' => 'nested content'],
        ]));
    }
}
