<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor;

use PrototypeIntegration\PrototypeIntegration\DataProcessing\ProcessorRunner;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class GeneralContentElementProcessor
{
    public function __construct(
        protected ContentElementProcessorFactory $processorFactory,
        protected RecordFactory $recordFactory,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        array $configuration,
        Record $contentElement,
    ): object|array|string|null {
        return $this->processContentElement($request, $configuration, $contentElement, $contentElement->getMainType());
    }

    public function processRow(
        ServerRequestInterface $request,
        array $configuration,
        array $row,
        string $table,
    ): object|array|string|null {
        return $this->processContentElement($request, $configuration, $row, $table);
    }

    protected function processContentElement(
        ServerRequestInterface $request,
        array $configuration,
        Record|array $contentElement,
        string $table,
    ): object|array|string|null {
        $contentObjectRenderer = $this->createNewContentObjectRenderer($request, $contentElement, $table);
        $request = $request->withAttribute('currentContentObject', $contentObjectRenderer);

        if (isset($configuration['_typoScriptNodeValue'])) {
            $typoScriptValue = $configuration['_typoScriptNodeValue'];
            unset($configuration['_typoScriptNodeValue']);
            $typoScriptConfiguration = GeneralUtility::makeInstance(TypoScriptService::class)->convertPlainArrayToTypoScriptArray($configuration);
        } else {
            $cType = $contentElement instanceof Record ? $contentElement->getRecordType() : $contentElement['CType'] ?? null;
            if (!isset($cType)) {
                throw new \RuntimeException('Unable to determine content element type');
            }

            $typoScript = $request->getAttribute('frontend.typoscript')->getSetupArray();
            $table = $contentObjectRenderer->getCurrentTable();
            $typoScriptValue = $typoScript[$table . '.'][$cType] ?? null;
            $typoScriptConfiguration = $typoScript[$table . '.'][$cType . '.'] ?? [];
        }
        if (isset($typoScriptValue)) {
            return $this->renderWithTypoScript(
                $request,
                $typoScriptConfiguration,
                $contentElement,
                $typoScriptValue,
                $configuration,
            );
        }

        return $this->renderWithPtiProcessor($request, $configuration, $contentElement);
    }

    protected function createNewContentObjectRenderer(
        ServerRequestInterface $request,
        array|Record $contentElement,
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

        $data = $contentElement instanceof Record ? $contentElement->getRawRecord()->toArray(true) : $contentElement;
        $contentObjectRenderer->start($data, $table);
        return $contentObjectRenderer;
    }

    protected function renderWithPtiProcessor(
        ServerRequestInterface $request,
        array $configuration,
        array|Record $contentElement,
    ): object|array|string|null {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');
        $processor = isset($configuration['processor'])
            ? $this->processorFactory->getProcessor($configuration['processor'])
            : ($contentElement instanceof Record
                ? $this->processorFactory->getProcessorForRecord($contentElement)
                : $this->processorFactory->getProcessorForRecord($contentObjectRenderer->getCurrentTable(), $contentElement));

        return $processor?->__invoke(
            $request,
            $configuration,
            $contentElement,
        );
    }

    protected function renderWithTypoScript(
        ServerRequestInterface $request,
        array $typoScriptConfiguration,
        Record|array $contentElement,
        string $typoScriptValue,
        array $processorConfiguration,
    ): object|array|string|null {
        switch ($typoScriptValue) {
            case 'PTI':
                return $this->processPtiContentObjectData(
                    $request,
                    $typoScriptConfiguration,
                );
            case 'PTI_OBJECT':
                $contentObjectRenderer = $request->getAttribute('currentContentObject');
                $record = $contentElement instanceof Record
                    ? $contentElement
                    : $this->recordFactory->createResolvedRecordFromDatabaseRow(
                        $contentObjectRenderer->getCurrentTable(),
                        $contentObjectRenderer->data,
                    );
                return $this->renderWithPtiProcessor(
                    $request,
                    $typoScriptConfiguration,
                    $record,
                );
            default:
                return $this->renderNonPtiContentObject(
                    $request,
                    $typoScriptConfiguration,
                    $typoScriptValue,
                    $processorConfiguration,
                );
        }
    }

    protected function processPtiContentObjectData(
        ServerRequestInterface $request,
        array $contentObjectConfiguration,
    ): ?array {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');
        $this->lastChanged($request, $contentObjectRenderer->data);

        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $plainContentObjectConfiguration = $typoScriptService->convertTypoScriptArrayToPlainArray($contentObjectConfiguration);

        /** @var ProcessorRunner $processorRunner */
        $processorRunner = GeneralUtility::makeInstance(ProcessorRunner::class);
        $processedData = $processorRunner->processData($contentObjectRenderer, $plainContentObjectConfiguration, $contentObjectRenderer->getCurrentTable());
        if ($processedData === null) {
            return null;
        }
        if (!isset($processedData['_templateName']) && isset($contentObjectConfiguration['templateName'])) {
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
