<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor\Event;

use TYPO3\CMS\Core\Resource\FileInterface;

class FileMetadataProcessorEvent
{
    public function __construct(
        protected FileInterface $file,
        protected array $metaData,
    ) {
    }

    public function getFile(): FileInterface
    {
        return $this->file;
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function setMetaData(array $metaData): void
    {
        $this->metaData = $metaData;
    }
}
