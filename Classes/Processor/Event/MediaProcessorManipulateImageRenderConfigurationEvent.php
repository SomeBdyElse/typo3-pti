<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor\Event;

use TYPO3\CMS\Core\Resource\FileInterface;

class MediaProcessorManipulateImageRenderConfigurationEvent
{
    protected FileInterface $mediaElement;

    protected array $imageConfiguration;

    public function __construct(FileInterface $mediaElement, array $imageConfiguration)
    {
        $this->mediaElement = $mediaElement;
        $this->imageConfiguration = $imageConfiguration;
    }

    public function getMediaElement(): FileInterface
    {
        return $this->mediaElement;
    }

    public function setMediaElement(FileInterface $mediaElement): void
    {
        $this->mediaElement = $mediaElement;
    }

    public function getImageConfiguration(): array
    {
        return $this->imageConfiguration;
    }

    public function setImageConfiguration(array $imageConfiguration): void
    {
        $this->imageConfiguration = $imageConfiguration;
    }
}
