<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CompoundProcessor implements PtiDataProcessor
{
    protected ContentObjectRenderer $contentObjectRenderer;

    public function __construct(
        protected TypoScriptService $typoScriptService,
        protected GeneralContentElementProcessor $contentElementProcessor,
    ) {
    }

    public function injectContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer)
    {
        $this->contentObjectRenderer = $contentObjectRenderer;
    }

    public function process(array $data, array $configuration): ?array
    {
        $processorConfiguration = [];
        foreach (['_overrideUncachedFormat'] as $processorConfigurationKey) {
            if (isset($configuration[$processorConfigurationKey])) {
                $processorConfiguration[$processorConfigurationKey] = $configuration[$processorConfigurationKey];
                unset($configuration[$processorConfigurationKey]);
            }
        }

        $contentData = $this->gatherContentData(
            $processorConfiguration,
            $configuration,
            $data,
            $this->contentObjectRenderer->getCurrentTable()
        );
        return $contentData;
    }

    /**
     * @param array $configuration
     * @param array $data
     * @return array
     */
    protected function gatherContentData(
        array $processorConfiguration,
        array $configuration,
        array $data,
        string $table
    ): array {
        $contentData = [];
        foreach ($configuration as $nodeKey => $node) {
            if (is_array($node)) {
                $nodeValue = $node['_typoScriptNodeValue'] ?? null;
                switch ($nodeValue) {
                    case null:
                        if (is_array($node)) {
                            $subContentData = $this->gatherContentData($processorConfiguration, $node, $data, $table);
                            if (!empty($subContentData)) {
                                $contentData[$nodeKey] = $subContentData;
                            }
                        }
                        break;
                    case 'PTI_CONTENT':
                        $contentData[$nodeKey] = $this->renderContentSection($processorConfiguration, $node);
                        break;
                    default:
                        $contentObjectResult = $this->contentElementProcessor->processRow(
                            $this->contentObjectRenderer->getRequest()->withAttribute('currentContentObject', $this->contentObjectRenderer),
                            array_replace($node, $processorConfiguration),
                            $data,
                            $table,
                        );
                        if ($contentObjectResult !== null) {
                            $contentData[$nodeKey] = $contentObjectResult;
                        }
                }
            } elseif (is_string($node)) {
                $contentData[$nodeKey] = $node;
            }
        }

        return $contentData;
    }

    protected function renderContentSection(array $processorConfiguration, $configuration): array
    {
        $configuration = $this->typoScriptService->convertPlainArrayToTypoScriptArray($configuration);
        $table = $configuration['table'];

        $contentData = [];
        $records = $this->contentObjectRenderer->getRecords($table, $configuration['select.']);
        foreach ($records as $record) {
            $result = $this->contentElementProcessor->processRow(
                $this->contentObjectRenderer->getRequest()->withAttribute('currentContentObject', $this->contentObjectRenderer),
                $processorConfiguration,
                $record,
                $table,
            );
            if (isset($result)) {
                $contentData[] = $result;
            }
        }
        return $contentData;
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
