<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Domain\Record;

class RecordProcessorRegistry
{
    public function __construct(
        protected array $processors = [],
    ) {
    }

    /**
     * @param class-string $processorClassName
     */
    public function addProcessor(
        string $mainType,
        string $recordType,
        string $processorClassName,
    ): void {
        $this->processors[$mainType][$recordType] = $processorClassName;
    }

    /**
     * @return class-string|null
     */
    public function getProcessorClassNameForType(string $mainType, string $recordType): ?string
    {
        return $this->processors[$mainType][$recordType] ?? null;
    }

    /**
     * @return class-string|null
     */
    public function getProcessorClassNameForRawRecord(string $mainType, array $record): ?string
    {
        $typeField = $GLOBALS['TCA'][$mainType]['ctrl']['type'] ?? null;
        if (!is_string($typeField) || !isset($record[$typeField])) {
            return null;
        }

        return $this->getProcessorClassNameForType($mainType, (string)$record[$typeField]);
    }

    /**
     * @return class-string|null
     */
    public function getProcessorClassNameForRecord(Record $record): ?string
    {
        $recordType = $record->getRecordType();
        if (!is_string($recordType)) {
            return null;
        }

        return $this->getProcessorClassNameForType($record->getMainType(), $recordType);
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }
}
