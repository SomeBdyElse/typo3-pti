<?php declare(strict_types = 1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\Resource\FileCollector;

/**
 * The Picture processor takes an image file and a some TypoScript
 * configuration to produce an array that contains the images meta
 * information as well as the image in all configured variants.
 *
 * Sample TypoScript configuration:
 * default {
 *   maxW = 600
 *   maxH = 600
 *   cropVariant = default
 * }
 * variants {
 *   5 {
 *     mediaQuery = screen-width(max: 320px)
 *     config {
 *       maxW = 320
 *       maxH = 320
 *       cropVariant = mobile
 *     }
 *   }
 * }
 */
class PictureProcessor
{
    protected ImageProcessor $imageProcessor;

    protected FileMetadataProcessor $fileMetaDataProcessor;

    public function __construct(
        ImageProcessor $imageProcessor,
        FileMetadataProcessor $fileMetaDataProcessor
    ) {
        $this->imageProcessor = $imageProcessor;
        $this->fileMetaDataProcessor = $fileMetaDataProcessor;
    }

    /**
     * @param $table string Name of the referencing table
     * @param $fieldName string Name of the referencing field
     * @param $row array The referencing row
     * @param $configuration
     * @return array
     */
    public function renderPicturesForRelation(string $table, string $fieldName, array $row, array $configuration = [])
    {
        // Get image files
        $fileCollector = $this->getFileCollector();
        $fileCollector->addFilesFromRelation($table, $fieldName, $row);
        $images = $fileCollector->getFiles();

        // Process pictures
        $pictures = [];
        foreach ($images as $image) {
            $pictures[] = $this->renderPicture($image, $configuration);
        }
        return $pictures;
    }

    public function renderPicture(FileInterface $image, $configuration): array
    {
        $assetOptions['default'] = $this->imageProcessor->renderImage(
            $image,
            isset($configuration['default']) ? $configuration['default'] : []
        );

        if (isset($configuration['variants']) && !empty($configuration['variants'])) {
            foreach ($configuration['variants'] as $variant) {
                $assetOptions['variants'][] = $this->imageProcessor->renderImage(
                    $image,
                    $variant['config']
                );
            }
        }

        $assetOptions['metaData'] = $this->fileMetaDataProcessor->processFile($image);

        $signalValues = $this->emitActionSignal(
            'afterRenderPicture',
            [
                'assetOptions' => $assetOptions,
                'image' => $image
            ]
        );

        return $signalValues['assetOptions'];
    }

    protected function getFileCollector(): FileCollector
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(FileCollector::class);
    }

    /**
     * Emits signal for various actions
     *
     * @param string $signalName name of the signal slot
     * @param array $signalArguments arguments for the signal slot
     * @return array
     */
    protected function emitActionSignal($signalName, array $signalArguments)
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);

        return $signalSlotDispatcher->dispatch(
            __CLASS__,
            $signalName,
            $signalArguments
        );
    }
}
