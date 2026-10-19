<?php

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use PrototypeIntegration\PrototypeIntegration\View\RecordProcessorRegistry;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordProcessorFactory
{
    public function __construct(
        protected RecordProcessorRegistry $processorRegistry,
        protected ?ContainerInterface $processorLocator = null,
    ) {
    }

    public function getProcessor(string $processorClassName): PtiRecordProcessor
    {
        if ($this->processorLocator?->has($processorClassName)) {
            return $this->processorLocator->get($processorClassName);
        }

        return GeneralUtility::makeInstance($processorClassName);
    }

    public function getProcessorForRecord(string|Record $mainTypeOrRecord, ?array $record = null): ?PtiRecordProcessor
    {
        $processorClassName = $mainTypeOrRecord instanceof Record
            ? $this->processorRegistry->getProcessorClassNameForRecord($mainTypeOrRecord)
            : $this->processorRegistry->getProcessorClassNameForRawRecord($mainTypeOrRecord, $record ?? []);
        if (!isset($processorClassName)) {
            return null;
        }

        return $this->getProcessor($processorClassName);
    }
}
