<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperInterface;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;

class VideoProcessor
{
    public function __construct(
        protected FileMetadataProcessor $fileMetaDataProcessor,
        protected OnlineMediaHelperRegistry $onlineMediaHelperRegistry
    ) {
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function renderVideo(FileInterface $file): ?array
    {
        $src = $this->getPublicVideoUri($file);
        if (! is_string($src)) {
            return null;
        }

        $videoData = [
            'default' => [
                'src' => $src,
                'type' => $file->getMimeType(),
            ],
            'metaData' => $this->fileMetaDataProcessor->processFile(($file)),
        ];

        return $videoData;
    }

    /**
     * @param FileInterface $file
     * @return string
     */
    protected function getPublicVideoUri(FileInterface $file): ?string
    {
        switch ($file->getMimeType()) {
            case 'video/mp4':
            case 'video/webm':
                return $this->renderMp4Video($file);
            case 'video/youtube':
                return $this->renderYoutubeVideo($file);
            case 'video/vimeo':
                return $this->renderVimeoVideo($file);
        }

        return null;
    }

    /**
     * Returns the public url of a mp4 file or null if the file is missing
     *
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @return string
     */
    protected function renderMp4Video(FileInterface $file): ?string
    {
        $publicUrl = null;

        if (!$file->isMissing()) {
            $publicUrl = $file->getPublicUrl();
        }

        return $publicUrl;
    }

    /**
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @return string
     */
    protected function renderYoutubeVideo(FileInterface $file): string
    {
        if ($file instanceof FileReference) {
            $orgFile = $file->getOriginalFile();
        } else {
            $orgFile = $file;
        }

        return sprintf(
            'https://www.youtube-nocookie.com/embed/%s',
            $this->getOnlineMediaHelper($file)->getOnlineMediaId($orgFile)
        );
    }

    protected function renderVimeoVideo(FileInterface $file): string
    {
        if ($file instanceof FileReference) {
            $orgFile = $file->getOriginalFile();
        } else {
            $orgFile = $file;
        }

        return sprintf(
            'https://player.vimeo.com/video/%s?dnt=1',
            $this->getOnlineMediaHelper($file)->getOnlineMediaId($orgFile)
        );
    }

    /**
     * Get the right online media helper for a given file
     * @param FileInterface $file
     * @return OnlineMediaHelperInterface|null
     */
    protected function getOnlineMediaHelper(FileInterface $file): ?OnlineMediaHelperInterface
    {
        if ($file instanceof FileReference) {
            $file = $file->getOriginalFile();
        }
        if ($file instanceof File) {
            $mediaHelper = $this->onlineMediaHelperRegistry->getOnlineMediaHelper($file);
            if ($mediaHelper) {
                return $mediaHelper;
            }
        }

        return null;
    }
}
