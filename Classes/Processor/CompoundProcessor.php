<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use PrototypeIntegration\PrototypeIntegration\DataProcessing\ProcessorRunner;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CompoundProcessor implements PtiDataProcessor
{
    protected ContentObjectRenderer $contentObjectRenderer;

    public function __construct(
        protected TypoScriptService $typoScriptService,
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
                    case 'PTI':
                        $contentData[$nodeKey] = $this->processPtiContentObjectData($node, $data, $table);
                        break;
                    default:
                        $contentObjectConfiguration = $this->typoScriptService->convertPlainArrayToTypoScriptArray(
                            $node
                        );
                        $contentObjectResult = $this->renderContentObject(
                            $processorConfiguration,
                            $nodeValue,
                            $contentObjectConfiguration,
                            $table,
                            $data
                        );
                        if (isset($contentObjectResult)) {
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
            $cType = $record['CType'];
            $typoScript = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray();
            $value = $typoScript[$table . '.'][$cType];
            $property = $typoScript[$table . '.'][$cType . '.'];

            switch ($value) {
                case 'PTI':
                    $contentObjectResult = $this->processPtiContentObjectData($property, $record, $table);
                    if (isset($contentObjectResult)) {
                        $contentData[] = $contentObjectResult;
                    }
                    break;
                default:
                    $contentResponse = $this->renderContentObject(
                        $processorConfiguration,
                        $value,
                        $property,
                        $table,
                        $record
                    );
                    $contentData[] = $contentResponse;
            }
        }
        return $contentData;
    }

    protected function processPtiContentObjectData($conf, $record, $table)
    {
        /** @var ContentObjectRenderer $contentObjectRenderer */
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->start($record, $table);
        $this->lastChanged($contentObjectRenderer, $table, $record);

        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $plainConf = $typoScriptService->convertTypoScriptArrayToPlainArray($conf);

        /** @var ProcessorRunner $processorRunner */
        $processorRunner = GeneralUtility::makeInstance(ProcessorRunner::class);
        $processedData = $processorRunner->processData($contentObjectRenderer, $plainConf, $table);
        if (!isset($processedData['_templateName']) && isset($conf['templateName'])) {
            $processedData['_templateName'] = $conf['templateName'];
        }
        return $processedData;
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @param $nodeValue
     * @param array $contentObjectConfiguration
     * @param string $table
     * @param array $data
     * @return mixed
     */
    protected function renderContentObject(array $processorConfiguration, $nodeValue, array $contentObjectConfiguration, string $table, array $data)
    {
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        // Force content object renderer to render json, so we can parse the result
        $data['_pti_format'] = 'json';

        // For uncached rendering, the output of the single content object needs to match the global output format
        if (isset($processorConfiguration['_overrideUncachedFormat'])) {
            $data['_pti_format_uncached'] = $processorConfiguration['_overrideUncachedFormat'];
        }
        $contentObjectRenderer->start($data, $table);
        $contentObjectResult = $contentObjectRenderer->cObjGetSingle(
            $nodeValue,
            $contentObjectConfiguration
        );
        $this->lastChanged($contentObjectRenderer, $table, $data);

        $data = json_decode($contentObjectResult, true);
        if ($data !== null) {
            return $data;
        }

        return $contentObjectResult;
    }

    protected function lastChanged(ContentObjectRenderer $contentObjectRenderer, string $table, array $data)
    {
        $timestampeColumn = $GLOBALS['TCA'][$table]['ctrl']['tstamp'] ?? 'tstamp';
        if (isset($data[$timestampeColumn])) {
            $contentObjectRenderer->lastChanged($data[$timestampeColumn]);
        }
    }
}
