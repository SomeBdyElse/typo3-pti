<?php declare(strict_types = 1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\Resource\FileCollector;

class MediaProcessor
{
    protected PictureProcessor $pictureProcessor;

    protected VideoProcessor $videoProcessor;

    protected Dispatcher $signalDispatcher;

    public function __construct(
        PictureProcessor $pictureProcessor,
        VideoProcessor $videoProcessor,
        Dispatcher $signalDispatcher
    ) {
        $this->pictureProcessor = $pictureProcessor;
        $this->videoProcessor = $videoProcessor;
        $this->signalDispatcher = $signalDispatcher;
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
        $signalValues = [
            'mediaElement' => $mediaElement,
            'imageConfiguration' => $imageConfiguration
        ];
        $signalValues = $this->emitActionSignal(
            'manipulateImageRenderConfiguration',
            $signalValues
        );

        $mediaData = [
            'uid' => $signalValues['mediaElement']->getProperty('uid'),
            'type' => 'image',
            'image' => $this->pictureProcessor->renderPicture(
                $signalValues['mediaElement'],
                $signalValues['imageConfiguration']
            )
        ];

        if (! empty($mediaData) && ! empty($imageThumbnailConfiguration)) {
            $mediaData['thumbnail'] = $this->pictureProcessor->renderPicture(
                $signalValues['mediaElement'],
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

            $signalData = [
                'mediaFile' => $mediaElement,
                'mediaData' => $mediaData,
                'parentObject' => $this,
                'imageConfig' => $posterThumbnailConfiguration,
            ];
            $signalData = $this->signalDispatcher->dispatch(
                __CLASS__,
                __FUNCTION__ . 'AfterRender',
                $signalData
            );

            return $signalData['mediaData'];
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
