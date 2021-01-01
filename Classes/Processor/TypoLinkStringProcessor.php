<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

class TypoLinkStringProcessor
{
    protected TypoLinkCodecService $typoLinkCodecService;

    protected ContentObjectRenderer $contentObject;

    public function __construct(
        TypoLinkCodecService $typoLinkCodecService,
        ContentObjectRenderer $contentObject
    ) {
        $this->typoLinkCodecService = $typoLinkCodecService;
        $this->contentObject = $contentObject;
    }

    public function processTypoLinkString(string $typoLinkString): ?array
    {
        $typoLinkParts = $this->typoLinkCodecService->decode($typoLinkString);
        $uri = $this->contentObject->typoLink_URL(
            [
                'parameter' => $typoLinkParts['url'],
                'additionalParams' => $typoLinkParts['additionalParams']
            ]
        );

        if (empty($uri)) {
            return null;
        }

        return [
            'config' => [
                'uri' => $uri,
                'target' => $typoLinkParts['target'],
                'class' => $typoLinkParts['class'],
                'title' => $typoLinkParts['title']
            ]
        ];
    }
}
