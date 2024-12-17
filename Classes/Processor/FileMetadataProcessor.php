<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Core\Resource\FileInterface;

class FileMetadataProcessor
{
    public function processFile(FileInterface $file): array
    {
        $properties = [
            'title' => 'title',
            'description' => 'description',
            'caption' => 'caption',
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

        if (isset($metaData['caption']) && !isset($metaData['description'])) {
            $metaData['description'] = $metaData['caption'];
        }

        return $metaData;
    }
}
