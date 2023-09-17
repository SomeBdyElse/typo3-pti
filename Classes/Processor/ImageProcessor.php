<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use UnexpectedValueException;

class ImageProcessor
{
    protected ContentObjectRenderer $contentObject;

    protected TypoScriptFrontendController $tsfe;

    /**
     * @param ContentObjectRenderer $contentObject
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct(ContentObjectRenderer $contentObject)
    {
        $this->contentObject = $contentObject;
        $this->tsfe = $GLOBALS['TSFE'];
    }

    public function renderImage(FileInterface $file, array $conf = []): array
    {
        $defaultImageResource = $this->contentObject->getImgResource($file, $conf);
        if (is_null($defaultImageResource) || !isset($defaultImageResource[3])) {
            throw new UnexpectedValueException(
                sprintf('An undefined error occurred during processing the asset with identifier "%s"', $file->getIdentifier()),
                1_678_088_092
            );
        }

        $retinaImageResource = self::renderRetinaImage($file, $conf);

        $assetOptions = [
            'uri' => [
                'default' => $defaultImageResource[3],
                'retina2x' => $retinaImageResource,
            ],
            'width' => $defaultImageResource[0],
            'height' => $defaultImageResource[1],
            'ratio' => $defaultImageResource[0] / $defaultImageResource[1]
        ];

        self::clearAssetOptions($assetOptions, $conf);

        return $assetOptions;
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @param array $configuration
     * @return string
     */
    protected function renderRetinaImage(FileInterface $file, array $configuration): string
    {
        $retinaConfiguration = $this->getImageConfigurationForRetina($configuration);
        $image = $this->contentObject->getImgResource($file, $retinaConfiguration);

        if (is_null($image) || !isset($image[3])) {
            throw new UnexpectedValueException(
                sprintf('An undefined error occurred during processing the asset with retina configuration for identifier "%s"', $file->getIdentifier()),
                1_678_787_266
            );
        }

        return $image[3];
    }

    /**
     * @param array $asset
     * @param array $conf
     */
    protected function clearAssetOptions(array &$asset, array $conf = [])
    {
        if (isset($conf['mediaQuery'])) {
            $asset['mq'] = $conf['mediaQuery'];

            unset($asset['width']);
            unset($asset['height']);
        }
    }

    /**
     * Translate a TypoScript configuration for an imgResource function.
     * Doubles all values referring to the image dimensions like
     * width, height, …
     *
     * @param array $defaultConfig
     * @return array retina config0
     */
    protected function getImageConfigurationForRetina(array $defaultConfig = []): array
    {
        $retinaConfig = $defaultConfig;
        foreach (['maxW', 'maxH', 'width', 'height'] as $configKey) {
            if (isset($defaultConfig[$configKey])) {
                $defaultValue = $defaultConfig[$configKey];

                // match strings like "100c-300" and transform them to "200c-600"
                $splitRegexp = '/^([0-9]*)([cm]?)([+-]?)([0-9]*)$/';
                preg_match($splitRegexp, $defaultValue, $matches);
                list($_, $value, $cropMode, $cropOffsetDirection, $cropOffsetValue) = $matches;

                $newValue = 2 * ((int)$value);

                if ((int)$cropOffsetValue > 0) {
                    $newCropOffsetDirection = $cropOffsetDirection;
                    $newCropOffsetValue = 2 * (int)$cropOffsetValue;
                } else {
                    $newCropOffsetDirection = '';
                    $newCropOffsetValue = '';
                }

                $retinaConfig[$configKey] = $newValue . $cropMode . $newCropOffsetDirection . $newCropOffsetValue;
            }
        }
        return $retinaConfig;
    }
}
