<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Domain\Record;

class ContentElementProcessorRegistry
{
    public function __construct(
        protected array $processors = [],
    ) {
    }

    /**
     * @param class-string $processorClassName
     */
    public function addProcessor(
        string $table,
        string $type,
        string $processorClassName,
    ): void {
        $this->processors[$table][$type] = $processorClassName;
    }

    /**
     * @return class-string|null
     */
    public function getProcessorClassNameForTableAndType(string $table, string $type): ?string
    {
        return $this->processors[$table][$type] ?? null;
    }

    /**
     * @return class-string|null
     */
    public function getProcessorClassNameForRawRecord(string $table, array $record): ?string
    {
        $typeField = $GLOBALS['TCA'][$table]['ctrl']['type'] ?? null;
        if (!is_string($typeField) || !isset($record[$typeField])) {
            return null;
        }

        return $this->getProcessorClassNameForTableAndType($table, (string)$record[$typeField]);
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

        return $this->getProcessorClassNameForTableAndType($record->getMainType(), $recordType);
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }
}
