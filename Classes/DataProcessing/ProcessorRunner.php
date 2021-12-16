<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\DataProcessing;

use PrototypeIntegration\PrototypeIntegration\DataProcessing\Event\ProcessorRunnerRanEvent;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ProcessorRunner
{
    protected EventDispatcher $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function processData(ContentObjectRenderer $contentObjectRenderer, array $conf, ?string $table): ?array
    {
        $data = $contentObjectRenderer->data;
        foreach ($conf['dataProcessors'] ?: [] as $dataProcessorConfiguration) {
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
                $table
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
        ContentObjectRenderer $contentObjectRenderer
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

        $data = $dataProcessor->process($contentObjectRenderer->data, $configuration);
        return $data;
    }
}
