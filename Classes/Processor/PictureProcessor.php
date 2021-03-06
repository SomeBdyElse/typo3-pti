<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ImageProcessor $imageProcessor,
        FileMetadataProcessor $fileMetaDataProcessor,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->imageProcessor = $imageProcessor;
        $this->fileMetaDataProcessor = $fileMetaDataProcessor;
        $this->eventDispatcher = $eventDispatcher;
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
        $result = [];
        $result['default'] = $this->imageProcessor->renderImage(
            $image,
            isset($configuration['default']) ? $configuration['default'] : []
        );

        if (isset($configuration['variants']) && !empty($configuration['variants'])) {
            foreach ($configuration['variants'] as $variant) {
                if (!isset($result['variants'])) {
                    $result['variants'] = [];
                }
                $result['variants'][] = $this->imageProcessor->renderImage(
                    $image,
                    $variant['config']
                );
            }
        }

        $result['metaData'] = $this->fileMetaDataProcessor->processFile($image);

        $event = new Event\PictureProcessorRenderedEvent($image, $result);
        $event = $this->eventDispatcher->dispatch($event);
        $result = $event->getResult();
        return $result;
    }

    protected function getFileCollector(): FileCollector
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(FileCollector::class);
    }
}
