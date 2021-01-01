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
    protected ContentObjectRenderer $contentObjectRenderer;

    protected EventDispatcher $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function injectContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer)
    {
        $this->contentObjectRenderer = $contentObjectRenderer;
    }

    public function processData(array $data, array $conf, ?string $table): ?array
    {
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

            $data = $this->runDataProcessor($dataProcessorClassName, $dataProcessorConfiguration, $data, $table);
            if (is_null($data)) {
                return null;
            }
        }

        if ($this->contentObjectRenderer->getCurrentTable() == 'tt_content') {
            $uid = $this->contentObjectRenderer->data['_LOCALIZED_UID'] ?? $this->contentObjectRenderer->data['uid'] ?? null;
            if (isset($uid)) {
                $data['uid'] = $uid;
            }
        }

        $data = $this->eventDispatcher->dispatch(new ProcessorRunnerRanEvent($data))->getData();

        return $data;
    }

    protected function runDataProcessor(string $className, array $configuration, array $data, string $table): ?array
    {
        // Instantiate class
        $dataProcessor = GeneralUtility::makeInstance($className);

        if (! $dataProcessor instanceof PtiDataProcessor) {
            throw new \RuntimeException(
                'Class ' . get_class($dataProcessor) . ' must implement Interface ' . PtiDataProcessor::class,
                1542889749
            );
        }

        if (method_exists($dataProcessor, 'injectContentObjectRenderer')) {
            if (! isset($this->contentObjectRenderer)) {
                $this->contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
                $this->contentObjectRenderer->start($data, $table);
            }
            $dataProcessor->injectContentObjectRenderer($this->contentObjectRenderer);
        }

        $data = $dataProcessor->process($data, $configuration);
        return $data;
    }
}
