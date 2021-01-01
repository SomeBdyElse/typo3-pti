<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\DataProcessing;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ProcessorRunner
{
    protected Dispatcher $signalSlotDispatcher;

    protected ContentObjectRenderer $contentObjectRenderer;

    public function __construct(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
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

        list($data) = $this->signalSlotDispatcher->dispatch(__CLASS__, 'beforeRendering', [$data]);
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
