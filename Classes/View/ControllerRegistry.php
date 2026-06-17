<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

use TYPO3\CMS\Core\Domain\Record;

class ControllerRegistry
{
    public function __construct(
        protected array $controllers = [],
    ) {
    }

    /**
     * @param class-string $controllerClassName
     */
    public function addController(
        string $table,
        string $type,
        string $controllerClassName,
    ): void {
        $this->controllers[$table][$type] = $controllerClassName;
    }

    /**
     * @return class-string|null
     */
    public function getControllerClassNameForTableAndType(string $table, string $type): ?string
    {
        return $this->controllers[$table][$type] ?? null;
    }

    /**
     * @return class-string|null
     */
    public function getControllerClassNameForRawRecord(string $table, array $record): ?string
    {
        $typeField = $GLOBALS['TCA'][$table]['ctrl']['type'] ?? null;
        if (!is_string($typeField) || !isset($record[$typeField])) {
            return null;
        }

        return $this->getControllerClassNameForTableAndType($table, (string)$record[$typeField]);
    }

    /**
     * @return class-string|null
     */
    public function getControllerClassNameForRecord(Record $record): ?string
    {
        $recordType = $record->getRecordType();
        if (!is_string($recordType)) {
            return null;
        }

        return $this->getControllerClassNameForTableAndType($record->getMainType(), $recordType);
    }

    public function getControllers(): array
    {
        return $this->controllers;
    }
}
