<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use PrototypeIntegration\PrototypeIntegration\Formatter\StringFormatter;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Resource\FileCollector;

/**
 * The file processor takes an file and a some TypoScript
 * configuration to produce an array that contains the file meta
 * information.
 *
 * Sample TypoScript configuration:
 * formatSize {
 *   decimals = 0
 *   decimalSeparator = .
 *   thousandsSeparator = ,
 *   units = B,KB,MB,GB,TB,PB,EB,ZB,YB
 * }
 *
 * metaDataDescription {
 *  downloadDescriptionField = another_field_for_the_description
 *  downloadDescriptionFallbackField = description
 *
 *  maxCharacters = 130
 *  append = ...
 *  respectWordBoundaries = 1
 * }
 */
class FileProcessor
{
    protected ContentObjectRenderer $contentObject;

    protected FileSizeProcessor $fileSizeProcessor;

    protected TypoLinkStringProcessor $typoLinkStringProcessor;

    protected PreviewImageProcessor $previewImageProcessor;

    protected PictureProcessor $pictureProcessor;

    protected StringFormatter $stringFormatter;

    protected array $configuration;

    /**
     * @var array
     */
    protected $metaDataConfiguration = [
        'downloadDescriptionField' => 'description',
        'downloadDescriptionFallbackField' => '',
        'maxCharacters' => PHP_INT_MAX,
        'append' => '...',
        'respectWordBoundaries' => true
    ];

    /**
     * FileProcessor constructor.
     *
     * @param ContentObjectRenderer $contentObject
     * @param FileSizeProcessor $fileSizeProcessor
     * @param TypoLinkStringProcessor $typoLinkStringProcessor
     * @param PreviewImageProcessor $posterProcessor
     * @param PictureProcessor $pictureProcessor
     * @param StringFormatter $stringFormatter
     */
    public function __construct(
        ContentObjectRenderer $contentObject,
        FileSizeProcessor $fileSizeProcessor,
        TypoLinkStringProcessor $typoLinkStringProcessor,
        PreviewImageProcessor $posterProcessor,
        PictureProcessor $pictureProcessor,
        StringFormatter $stringFormatter
    ) {
        $this->contentObject = $contentObject;
        $this->fileSizeProcessor = $fileSizeProcessor;
        $this->typoLinkStringProcessor = $typoLinkStringProcessor;
        $this->previewImageProcessor = $posterProcessor;
        $this->pictureProcessor = $pictureProcessor;
        $this->stringFormatter = $stringFormatter;
    }

    /**
     * @param string $table The referencing table
     * @param string $fieldName The field name containing the reference
     * @param array $row The referencing row (must be from $table and include $fieldName)
     * @param array $configuration
     * @return array
     */
    public function renderFileCollection(string $table, string $fieldName, array $row, array $configuration = []): array
    {
        $this->configuration = $configuration;
        $this->setMetaDataConfiguration();

        $fileCollector = GeneralUtility::makeInstance(FileCollector::class);
        $fileCollector->addFilesFromRelation($table, $fieldName, $row);
        $files = $fileCollector->getFiles();

        $processedFiles = [];
        foreach ($files as $file) {
            $processedFiles[] = $this->getDownloadItem($file);
        }

        return $processedFiles;
    }

    /**
     * Retrieve the download item from the db.
     *
     * @param FileReference $item
     * @return array
     */
    protected function getDownloadItem(FileReference $item): array
    {
        $fileFormatConfiguration = $this->configuration['formatSize'] ?: [];
        $description = $this->getMetaDataDescription($item);

        $downloadItem = [
            'link' => [
                'metaData' => [
                    'description' => $description,
                    'name' => $item->getTitle(),
                    'extension' => $item->getExtension(),
                    'size' => $this->fileSizeProcessor->formatFileSize($item->getSize(), $fileFormatConfiguration)
                ]
            ],
        ];

        $linkString = $item->getPublicUrl() . ' _blank ' . ' ' . $item->getName();
        $linkConfig = $this->typoLinkStringProcessor->processTypoLinkString($linkString) ?: [];
        ArrayUtility::mergeRecursiveWithOverrule($downloadItem['link'], $linkConfig);

        $downloadImage = $this->previewImageProcessor->getPreviewImage($item, $this->configuration);
        if (isset($downloadImage)) {
            $downloadItem['image'] = $this->pictureProcessor->renderPicture(
                $downloadImage,
                $this->configuration['imageConfig']
            );
        }

        return $downloadItem;
    }

    /**
     * Retrieve the meta data description of a file. It's possible to use a another field as the
     * description-field. It's also possible to use an fallback field, if the defined field is not available
     * or empty.
     *
     * @param \TYPO3\CMS\Core\Resource\FileReference $item
     * @return string|null
     */
    protected function getMetaDataDescription(FileReference $item): ?string
    {
        $description = null;

        if (! empty($this->metaDataConfiguration['downloadDescriptionField'])
            && $item->hasProperty($this->metaDataConfiguration['downloadDescriptionField'])
        ) {
            $description = $item->getProperty($this->metaDataConfiguration['downloadDescriptionField']);
        }

        // fallback
        if ((empty($description) || is_null($description))
            && ! empty($this->metaDataConfiguration['downloadDescriptionFallbackField'])
            && $item->hasProperty($this->metaDataConfiguration['downloadDescriptionFallbackField'])
        ) {
            $description = $item->getProperty($this->metaDataConfiguration['downloadDescriptionFallbackField']);
        }

        $description = $this->stringFormatter->formatCrop($description, $this->metaDataConfiguration);

        return $description;
    }

    protected function setMetaDataConfiguration()
    {
        if (isset($this->configuration['metaDataDescription'])) {
            ArrayUtility::mergeRecursiveWithOverrule(
                $this->metaDataConfiguration,
                $this->configuration['metaDataDescription'],
                false,
                true,
                true
            );

            if (empty($this->metaDataConfiguration['downloadDescriptionField'])) {
                $this->metaDataConfiguration['downloadDescriptionField'] = 'description';
            }
        }
    }
}
