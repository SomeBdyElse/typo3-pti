<?php

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use PrototypeIntegration\PrototypeIntegration\View\ContentElementProcessorRegistry;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentElementProcessorFactory
{
    public function __construct(
        protected ContentElementProcessorRegistry $processorRegistry,
        protected ?ContainerInterface $processorLocator = null,
    ) {
    }

    public function getProcessor(string $processorClassName): object
    {
        if ($this->processorLocator?->has($processorClassName)) {
            return $this->processorLocator->get($processorClassName);
        }

        return GeneralUtility::makeInstance($processorClassName);
    }

    public function getProcessorForRecord(string|Record $tableOrRecord, ?array $record = null): ?object
    {
        $processorClassName = $tableOrRecord instanceof Record
            ? $this->processorRegistry->getProcessorClassNameForRecord($tableOrRecord)
            : $this->processorRegistry->getProcessorClassNameForRawRecord($tableOrRecord, $record ?? []);
        if (!isset($processorClassName)) {
            return null;
        }

        return $this->getProcessor($processorClassName);
    }
}
