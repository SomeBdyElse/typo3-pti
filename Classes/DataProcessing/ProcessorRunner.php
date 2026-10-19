<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\DataProcessing;

use PrototypeIntegration\PrototypeIntegration\DataProcessing\Event\ProcessorRunnerRanEvent;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\RecordProcessorFactory;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ProcessorRunner
{
    protected EventDispatcher $eventDispatcher;

    public function __construct(
        EventDispatcher $eventDispatcher,
        protected RecordProcessorFactory $processorFactory,
        protected RecordFactory $recordFactory,
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function processData(ContentObjectRenderer $contentObjectRenderer, array $conf, ?string $table): object|array|string|null
    {
        if (isset($conf['dataProcessors'])) {
            return $this->runDataProcessors($contentObjectRenderer, $conf, $table);
        }

        if (isset($conf['processor'])) {
            $processor = $this->processorFactory->getProcessor($conf['processor']);
        } else {
            $processor = $this->processorFactory->getProcessorForRecord($table, $contentObjectRenderer->data);
        }
        if (!isset($processor)) {
            return $contentObjectRenderer->data;
        }

        $request = $contentObjectRenderer->getRequest()->withAttribute('currentContentObject', $contentObjectRenderer);
        $record = $this->recordFactory->createResolvedRecordFromDatabaseRow($table, $contentObjectRenderer->data);

        return $processor->__invoke($request, $conf, $record);
    }

    public function processRecord(ContentObjectRenderer $contentObjectRenderer, array $conf, Record $record): object|array|string|null
    {
        if (isset($conf['dataProcessors'])) {
            // we have a record, but data processors only work with arrays. unwrap
            $table = $record->getMainType();
            $contentObjectRenderer->start($contentObjectRenderer->data, $table);
            return $this->runDataProcessors($contentObjectRenderer, $conf, $table);
        }

        if (isset($conf['processor'])) {
            $processor = $this->processorFactory->getProcessor($conf['processor']);
        } else {
            $processor = $this->processorFactory->getProcessorForRecord($record);
        }
        if (!isset($processor)) {
            return $record;
        }

        $request = $contentObjectRenderer->getRequest()->withAttribute('currentContentObject', $contentObjectRenderer);

        return $processor->__invoke($request, $conf, $record);
    }

    protected function runDataProcessors(ContentObjectRenderer $contentObjectRenderer, array $conf, ?string $table): ?array
    {
        $data = $contentObjectRenderer->data;
        foreach ($conf['dataProcessors'] ?? [] as $dataProcessorConfiguration) {
            // Get classname
            $dataProcessorClassName = null;
            if (is_string($dataProcessorConfiguration)) {
                $dataProcessorClassName = $dataProcessorConfiguration;
                $dataProcessorConfiguration = [];
            }
            if (isset($dataProcessorConfiguration['_typoScriptNodeValue'])) {
                $dataProcessorClassName = $dataProcessorConfiguration['_typoScriptNodeValue'];
                unset($dataProcessorConfiguration['_typoScriptNodeValue']);
            }
            if (!isset($dataProcessorClassName)) {
                throw new \RuntimeException('Missing className for dataProcessor', 1521297809618);
            }

            $data = $this->runDataProcessor(
                $dataProcessorClassName,
                $dataProcessorConfiguration,
                $contentObjectRenderer,
                $data
            );
            if (is_null($data)) {
                return null;
            }
        }

        if ($contentObjectRenderer->getCurrentTable() == 'tt_content') {
            $uid = $contentObjectRenderer->data['_LOCALIZED_UID'] ?? $contentObjectRenderer->data['uid'] ?? null;
            if (isset($uid)) {
                $data['uid'] = $uid;
            }
        }

        $data = $this->eventDispatcher->dispatch(new ProcessorRunnerRanEvent($data))->getData();

        return $data;
    }

    protected function runDataProcessor(
        string $className,
        array $configuration,
        ContentObjectRenderer $contentObjectRenderer,
        array $data
    ): ?array {
        // Instantiate class
        $dataProcessor = GeneralUtility::makeInstance($className);

        if (! $dataProcessor instanceof PtiDataProcessor) {
            throw new \RuntimeException(
                'Class ' . get_class($dataProcessor) . ' must implement Interface ' . PtiDataProcessor::class,
                1542889749
            );
        }

        if (method_exists($dataProcessor, 'injectContentObjectRenderer')) {
            $dataProcessor->injectContentObjectRenderer($contentObjectRenderer);
        }

        $data = $dataProcessor->process($data, $configuration);
        return $data;
    }
}
