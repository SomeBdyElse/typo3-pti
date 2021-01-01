<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class RichtextProcessor
{
    protected ContentObjectRenderer $contentObject;

    public function __construct(ContentObjectRenderer $contentObject)
    {
        $this->contentObject = $contentObject;
    }

    /**
     * Takes the markup produced by the TYPO3 Rich-text editor and
     * returns clean valid vanilla HTML for the FE.
     *
     * Includes link processing, etc.
     *
     * @param string|null $rteText
     * @return string
     */
    public function processRteText(?string $rteText): string
    {
        if (! isset($rteText)) {
            return '';
        }

        return $this->contentObject->parseFunc($rteText, [], '< lib.parseFunc_RTE');
    }
}
