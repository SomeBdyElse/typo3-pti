<?php

declare(strict_types=1);
namespace PrototypeIntegration\PrototypeIntegration\Processor\Event;

/**
 * Class ImageProcessorManipulateImgResourceResultEvent
 */
class ImageProcessorManipulateImgResourceResultEvent
{
    protected array $renderedResult;

    protected array $imageConfiguration;

    public function __construct(array $renderedResult, array $imageConfiguration)
    {
        $this->renderedResult = $renderedResult;
        $this->imageConfiguration = $imageConfiguration;
    }

    public function getRenderedResult(): array
    {
        return $this->renderedResult;
    }

    public function setRenderedResult(array $renderedResult): void
    {
        $this->renderedResult = $renderedResult;
    }

    public function getImageConfiguration(): array
    {
        return $this->imageConfiguration;
    }
}
