<?php

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use PrototypeIntegration\PrototypeIntegration\View\ControllerRegistry;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ControllerFactory
{
    public function __construct(
        protected ControllerRegistry $controllerRegistry,
        protected ?ContainerInterface $controllerLocator = null,
    ) {
    }

    public function getController(string $controllerClassName): object
    {
        if ($this->controllerLocator?->has($controllerClassName)) {
            return $this->controllerLocator->get($controllerClassName);
        }

        return GeneralUtility::makeInstance($controllerClassName);
    }

    public function getControllerForRecord(string|Record $tableOrRecord, ?array $record = null): ?object
    {
        $controllerClassName = $tableOrRecord instanceof Record
            ? $this->controllerRegistry->getControllerClassNameForRecord($tableOrRecord)
            : $this->controllerRegistry->getControllerClassNameForRawRecord($tableOrRecord, $record ?? []);
        if (!isset($controllerClassName)) {
            return null;
        }

        return $this->getController($controllerClassName);
    }
}
