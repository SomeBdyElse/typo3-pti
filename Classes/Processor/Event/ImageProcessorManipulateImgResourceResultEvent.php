<?php

declare(strict_types=1);
namespace PrototypeIntegration\PrototypeIntegration\Processor\Event;

/**
 * Class ImageProcessorManipulateImgResourceResultEvent
 */
class ImageProcessorManipulateImgResourceResultEvent
{
    protected array $renderedResult;

    public function __construct(array $renderedResult)
    {
        $this->renderedResult = $renderedResult;
    }

    public function getRenderedResult(): array
    {
        return $this->renderedResult;
    }

    public function setRenderedResult(array $renderedResult): void
    {
        $this->renderedResult = $renderedResult;
    }
}
