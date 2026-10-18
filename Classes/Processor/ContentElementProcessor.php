<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use PrototypeIntegration\PrototypeIntegration\DataProcessing\ProcessorRunner;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ContentElementProcessor
{
    public function __construct(
        protected ProcessorRunner $processorRunner,
    ) {
    }

    /**
     * Renders a given record by fetching the relevant $table.$type
     * configuration from TypoScript and running the processor defined therein.
     */
    public function processRecord(
        ServerRequestInterface $request,
        array $configuration,
        Record $record,
    ): object|array|string|null {
        return $this->processRecordOrRow($request, $configuration, $record, $record->getMainType());
    }

    /**
     * Renders a given database row by fetching the relevant $table.$type
     * configuration from TypoScript and running the processor defined therein.
     */
    public function processRow(
        ServerRequestInterface $request,
        array $configuration,
        array $row,
        string $table,
    ): object|array|string|null {
        return $this->processRecordOrRow($request, $configuration, $row, $table);
    }

    protected function processRecordOrRow(
        ServerRequestInterface $request,
        array $configuration,
        Record|array $recordOrRow,
        string $table,
    ): object|array|string|null {
        $contentObjectRenderer = $this->createNewContentObjectRenderer($request, $recordOrRow, $table);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        if (isset($configuration['_typoScriptNodeValue'])) {
            $typoScriptValue = $configuration['_typoScriptNodeValue'];
            unset($configuration['_typoScriptNodeValue']);
            $typoScriptConfiguration = GeneralUtility::makeInstance(TypoScriptService::class)->convertPlainArrayToTypoScriptArray($configuration);
        } else {
            $type = $recordOrRow instanceof Record ? $recordOrRow->getRecordType() : $recordOrRow['CType'] ?? null;
            if (!isset($type)) {
                throw new \RuntimeException('Unable to determine record type');
            }

            $typoScript = $request->getAttribute('frontend.typoscript')->getSetupArray();
            $table = $contentObjectRenderer->getCurrentTable();
            $typoScriptValue = $typoScript[$table . '.'][$type] ?? null;
            $typoScriptConfiguration = $typoScript[$table . '.'][$type . '.'] ?? [];
        }
        if (isset($typoScriptValue)) {
            return $this->renderWithTypoScript(
                $request,
                $typoScriptConfiguration,
                $recordOrRow,
                $table,
                $typoScriptValue,
                $configuration,
            );
        }

        return $this->renderWithPtiProcessor($request, $configuration, $recordOrRow, $table);
    }

    protected function createNewContentObjectRenderer(
        ServerRequestInterface $request,
        array|Record $record,
        string $table
    ): mixed {
        $parentContentObjectRenderer = $request->getAttribute('currentContentObject');
        $parentContentObjectRenderer = $parentContentObjectRenderer instanceof ContentObjectRenderer ? $parentContentObjectRenderer : null;
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->setRequest($request);
        if ($parentContentObjectRenderer !== null) {
            $contentObjectRenderer->setParent(
                $parentContentObjectRenderer->data,
                $parentContentObjectRenderer->currentRecord
            );
        }

        $data = $record instanceof Record ? $record->getRawRecord()->toArray(true) : $record;
        $contentObjectRenderer->start($data, $table);
        return $contentObjectRenderer;
    }

    protected function renderWithPtiProcessor(
        ServerRequestInterface $request,
        array $configuration,
        array|Record $recordOrRow,
        string $table,
    ): object|array|string|null {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');
        if (is_array($recordOrRow)) {
            return $this->processorRunner->processData(
                $contentObjectRenderer,
                $configuration,
                $table,
            );
        }

        return $this->processorRunner->processRecord(
            $contentObjectRenderer,
            $configuration,
            $recordOrRow,
        );
    }

    protected function renderWithTypoScript(
        ServerRequestInterface $request,
        array $typoScriptConfiguration,
        Record|array $recordOrRow,
        string $table,
        string $typoScriptValue,
        array $processorConfiguration,
    ): object|array|string|null {
        if ($typoScriptValue === 'PTI') {
            return $this->processPtiContentObjectData(
                $request,
                $typoScriptConfiguration,
                $recordOrRow,
                $table,
            );
        }
        return $this->renderNonPtiContentObject(
            $request,
            $typoScriptConfiguration,
            $typoScriptValue,
            $processorConfiguration,
        );
    }

    protected function processPtiContentObjectData(
        ServerRequestInterface $request,
        array $contentObjectConfiguration,
        Record|array $recordOrRow,
        string $table,
    ): object|array|string|null {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');
        $this->lastChanged($request, $contentObjectRenderer->data);

        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $plainContentObjectConfiguration = $typoScriptService->convertTypoScriptArrayToPlainArray($contentObjectConfiguration);

        if (is_array($recordOrRow)) {
            $processedData = $this->processorRunner->processData(
                $contentObjectRenderer,
                $plainContentObjectConfiguration,
                $table,
            );
        } else {
            $processedData = $this->processorRunner->processRecord(
                $contentObjectRenderer,
                $plainContentObjectConfiguration,
                $recordOrRow,
            );
        }

        if ($processedData === null) {
            return null;
        }
        if (is_array($processedData) && !isset($processedData['_templateName']) && isset($contentObjectConfiguration['templateName'])) {
            $processedData['_templateName'] = $contentObjectConfiguration['templateName'];
        }
        return $processedData;
    }

    protected function renderNonPtiContentObject(
        ServerRequestInterface $request,
        array $contentObjectConfiguration,
        string $nodeValue,
        array $processorConfiguration,
    ): object|array|string {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');
        $data = $contentObjectRenderer->data;

        // Force content object renderer to render json, so we can parse the result
        $data['_pti_format'] = 'json';

        // For uncached rendering, the output of the single content object needs to match the global output format
        if (isset($processorConfiguration['_overrideUncachedFormat'])) {
            $data['_pti_format_uncached'] = $processorConfiguration['_overrideUncachedFormat'];
        }

        $contentObjectRenderer->start($data, $contentObjectRenderer->getCurrentTable());
        $contentObjectResult = $contentObjectRenderer->cObjGetSingle(
            $nodeValue,
            $contentObjectConfiguration
        );
        $this->lastChanged($request, $data);

        $data = json_decode($contentObjectResult, true);
        if (is_array($data)) {
            return $data;
        }

        return $contentObjectResult;
    }

    protected function lastChanged(ServerRequestInterface $request, array $data): void
    {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');
        $timestampColumn = $GLOBALS['TCA'][$contentObjectRenderer->getCurrentTable()]['ctrl']['tstamp'] ?? 'tstamp';
        if (isset($data[$timestampColumn])) {
            $contentObjectRenderer->lastChanged($data[$timestampColumn]);
        }
    }
}
