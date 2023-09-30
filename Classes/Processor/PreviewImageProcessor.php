<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\Index\MetaDataRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Preview image processor
 *
 * Retrieves preview images for files and videos
 */
class PreviewImageProcessor
{
    public const FILE_PREVIEW_IMAGE_PROPERTY = 'preview_image';

    protected MetaDataRepository $metaDataRepository;

    protected FileRepository $fileRepository;

    public function __construct(
        MetaDataRepository $metaDataRepository,
        FileRepository $fileRepository
    ) {
        $this->metaDataRepository = $metaDataRepository;
        $this->fileRepository = $fileRepository;
    }

    public function getPreviewImage(FileInterface $file, array $configuration = []): ?FileInterface
    {
        if ($file->hasProperty(self::FILE_PREVIEW_IMAGE_PROPERTY)
            && !empty($file->getProperty(self::FILE_PREVIEW_IMAGE_PROPERTY))
        ) {
            $originalFile = $file instanceof FileReference ? $file->getOriginalFile() : $file;
            $metaData = $this->metaDataRepository->findByFile($originalFile);
            $metaDataUid = $metaData['uid'];

            $posterImages = $this->fileRepository->findByRelation(
                'sys_file_metadata',
                self::FILE_PREVIEW_IMAGE_PROPERTY,
                $metaDataUid
            );

            if (isset($posterImages[0])) {
                return $posterImages[0];
            }
        }

        if (isset($configuration['fallbackPreviewImage']) && ! empty($configuration['fallbackPreviewImage'])) {
            /**@var ResourceFactory $resourceFactory*/
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $file = $resourceFactory->retrieveFileOrFolderObject($configuration['fallbackPreviewImage']);
        }

        // If no explicit poster image is set, try to use the file itself
        $isRenderableFile =
            $file->getType() == AbstractFile::FILETYPE_IMAGE
            || GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $file->getExtension())
        ;
        if ($isRenderableFile) {
            return $file;
        }

        return null;
    }
}
