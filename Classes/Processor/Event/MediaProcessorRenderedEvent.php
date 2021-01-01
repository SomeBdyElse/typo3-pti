<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor\Event;

use PrototypeIntegration\PrototypeIntegration\Processor\MediaProcessor;
use TYPO3\CMS\Core\Resource\FileInterface;

class MediaProcessorRenderedEvent
{
    protected FileInterface $mediaFile;

    protected MediaProcessor $parentObject;

    protected array $imageConfig;

    protected array $mediaData;

    public function __construct(
        FileInterface $mediaFile,
        MediaProcessor $parentObject,
        array $imageConfig,
        array $mediaData
    ) {
        $this->mediaFile = $mediaFile;
        $this->parentObject = $parentObject;
        $this->imageConfig = $imageConfig;
        $this->mediaData = $mediaData;
    }

    public function getMediaFile(): FileInterface
    {
        return $this->mediaFile;
    }

    public function getParentObject(): MediaProcessor
    {
        return $this->parentObject;
    }

    public function getImageConfig(): array
    {
        return $this->imageConfig;
    }

    public function getMediaData(): array
    {
        return $this->mediaData;
    }

    public function setMediaData(array $mediaData): void
    {
        $this->mediaData = $mediaData;
    }
}
