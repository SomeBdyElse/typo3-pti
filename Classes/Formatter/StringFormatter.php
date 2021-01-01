<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Formatter;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class StringFormatter
{
    /**
     * Crop the given string with given configuration.
     *
     * Configuration array example:
     * [
     *  'maxCharacters' => 130,
     *  'append' => '...'
     *  'respectWordBoundaries' => true
     * ]
     *
     * @param string|null $value The value to format
     * @param array $config The cropping configuration
     * @return string The cropped string or null
     */
    public function formatCrop(?string $value, array $config = []): ?string
    {
        if (is_null($value)) {
            return $value;
        }

        $stringToCrop = $value;

        if (isset($config['maxCharacters'])
            && (int)$config['maxCharacters']
            && strlen($value) > (int)$config['maxCharacters']
        ) {
            /** @var ContentObjectRenderer $contentObject */
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $stringToCrop = $contentObject->crop(
                $value,
                (int)$config['maxCharacters'] . '|' . $config['append'] . '|' . (bool)$config['respectWordBoundaries']
            );
        }

        return $stringToCrop;
    }
}
