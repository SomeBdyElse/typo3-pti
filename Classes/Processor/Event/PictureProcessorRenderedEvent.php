<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor\Event;

use TYPO3\CMS\Core\Resource\FileInterface;

class PictureProcessorRenderedEvent
{
    protected FileInterface $image;

    protected array $result;

    public function __construct(
        FileInterface $image,
        array $result
    ) {
        $this->image = $image;
        $this->result = $result;
    }

    public function getImage(): FileInterface
    {
        return $this->image;
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): void
    {
        $this->result = $result;
    }
}
