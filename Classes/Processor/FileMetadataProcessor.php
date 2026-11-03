<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use PrototypeIntegration\PrototypeIntegration\Processor\Event\FileMetadataProcessorEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\FileInterface;

class FileMetadataProcessor
{
    public function __construct(
        protected EventDispatcher $eventDispatcher,
    ) {
    }

    public function processFile(FileInterface $file): array
    {
        $properties = [
            'title' => 'title',
            'description' => 'description',
            'copyright' => 'copyright',
            'link' => 'link',
            'alternative' => 'alternative',
            'fileType' => 'extension',
        ];

        $metaData = [];

        foreach ($properties as $key => $propertyName) {
            if ($file->hasProperty($propertyName)) {
                $value = $file->getProperty($propertyName);

                if (is_string($value) && strlen($value) > 0) {
                    $metaData[$key] = $value;
                }
            }
        }

        $event = new FileMetadataProcessorEvent($file, $metaData);
        $metaData = $this->eventDispatcher->dispatch($event)->getMetaData();

        return $metaData;
    }
}
