<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use PrototypeIntegration\PrototypeIntegration\Processor\Event\MediaProcessorManipulateImageRenderConfigurationEvent;
use PrototypeIntegration\PrototypeIntegration\Processor\Event\MediaProcessorRenderedEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Resource\FileCollector;

class MediaProcessor
{
    protected PictureProcessor $pictureProcessor;

    protected VideoProcessor $videoProcessor;

    protected EventDispatcher $eventDispatcher;

    public function __construct(
        PictureProcessor $pictureProcessor,
        VideoProcessor $videoProcessor,
        EventDispatcher $eventDispatcher
    ) {
        $this->pictureProcessor = $pictureProcessor;
        $this->videoProcessor = $videoProcessor;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param array $data
     * @param string $table
     * @param string $field
     * @param array $imageConfiguration
     * @param array $imageThumbnailConfiguration
     * @return array
     */
    public function renderMedia(
        array $data,
        $table = 'tt_content',
        $field = 'assets',
        array $imageConfiguration = [],
        array $imageThumbnailConfiguration = []
    ) {
        $result = [];
        $mediaElements = $this->getMediaElements($data, $table, $field);

        if (count($mediaElements) > 0) {
            foreach ($mediaElements as $mediaElement) {
                $mediaElementResult = $this->renderMediaElement(
                    $mediaElement,
                    $imageConfiguration,
                    $imageThumbnailConfiguration
                );
                if (isset($mediaElementResult)) {
                    $result[] = $mediaElementResult;
                }
            }
        }

        return $result;
    }

    public function renderMediaElement(
        FileInterface $mediaElement,
        array $imageConfiguration = [],
        array $imageThumbnailConfiguration = []
    ): ?array {
        /** @var FileInterface $mediaElement */
        $fileType = $this->getFileType($mediaElement);
        if ($fileType == AbstractFile::FILETYPE_IMAGE) {
            $mediaItem = $this->renderImageElement(
                $mediaElement,
                $imageConfiguration,
                $imageThumbnailConfiguration
            );
            return $mediaItem;
        }

        if ($fileType == AbstractFile::FILETYPE_VIDEO) {
            $mediaItem = $this->renderVideoElement($mediaElement, $imageThumbnailConfiguration);
            return $mediaItem;
        }

        return null;
    }

    protected function renderImageElement(
        FileInterface $mediaElement,
        array $imageConfiguration,
        array $imageThumbnailConfiguration
    ) {
        $event = new MediaProcessorManipulateImageRenderConfigurationEvent($mediaElement, $imageConfiguration);
        $event = $this->eventDispatcher->dispatch($event);
        $mediaElement = $event->getMediaElement();
        $imageConfiguration = $event->getImageConfiguration();

        $mediaData = [
            'uid' => $mediaElement->getProperty('uid'),
            'type' => 'image',
            'image' => $this->pictureProcessor->renderPicture(
                $mediaElement,
                $imageConfiguration
            )
        ];

        if (! empty($mediaData) && ! empty($imageThumbnailConfiguration)) {
            $mediaData['thumbnail'] = $this->pictureProcessor->renderPicture(
                $mediaElement,
                $imageThumbnailConfiguration
            );
        }

        return $mediaData;
    }

    protected function renderVideoElement(
        FileInterface $mediaElement,
        array $posterThumbnailConfiguration
    ): ?array {
        $video = $this->videoProcessor->renderVideo($mediaElement);

        if (is_array($video)) {
            $mediaData = [
                'uid' => $mediaElement->getProperty('uid'),
                'type' => 'video',
                'video' => $video,
            ];

            $mediaData = $this->eventDispatcher->dispatch(new MediaProcessorRenderedEvent(
                $mediaElement,
                $this,
                $posterThumbnailConfiguration,
                $mediaData
            ))->getMediaData();

            return $mediaData;
        }

        return null;
    }

    /**
     * Get media files (images, videos)
     *
     * @param array $data
     * @param string $table
     * @param string $field
     * @return array
     */
    protected function getMediaElements(array $data, $table = 'tt_content', $field = 'assets'): array
    {
        $fileCollector = $this->getFileCollector();
        $fileCollector->addFilesFromRelation($table, $field, $data);
        return $fileCollector->getFiles();
    }

    protected function getFileCollector(): FileCollector
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return GeneralUtility::makeInstance(FileCollector::class);
    }

    protected function getFileType(FileInterface $fileOrFileReference): int
    {
        if ($fileOrFileReference instanceof File) {
            return $fileOrFileReference->getType();
        }
        if ($fileOrFileReference instanceof FileReference) {
            return $fileOrFileReference->getOriginalFile()->getType();
        }

        return AbstractFile::FILETYPE_UNKNOWN;
    }
}
