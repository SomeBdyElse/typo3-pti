<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\Processor\PageElement;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class MenuProcessor implements PtiDataProcessor
{
    protected ContentObjectRenderer $contentObjectRenderer;

    protected TypoScriptService $typoScriptService;

    public function __construct(
        TypoScriptService $typoScriptService
    ) {
        $this->typoScriptService = $typoScriptService;
    }

    public function injectContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer)
    {
        $this->contentObjectRenderer = $contentObjectRenderer;
    }

    public function process(array $data, array $configuration): ?array
    {
        $defaultConfiguration = [
            'includePageData' => false
        ];
        $configuration = array_replace($defaultConfiguration, $configuration);

        $menuData = $this->getMenuData($configuration);
        if (!$configuration['includePageData']) {
            $menuData = $this->removePageData($menuData);
        }
        return $menuData;
    }

    protected function getMenuData(array $configuration): ?array
    {
        $menuDataFetcher = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\DataProcessing\MenuProcessor::class);

        $menuConfiguration = $this->typoScriptService->convertPlainArrayToTypoScriptArray(
            $configuration['menuConfiguration']
        );

        $menuData = $menuDataFetcher->process($this->contentObjectRenderer, [], $menuConfiguration, []);
        return $menuData['menu'] ?? null;
    }

    protected function removePageData($menuData): array
    {
        foreach ($menuData as $key => $menuItem) {
            unset($menuData[$key]['data']);

            if (isset($menuItem['children']) && count($menuItem['children'])) {
                $menuData[$key]['children'] = $this->removePageData($menuItem['children']);
            }
        }

        return $menuData;
    }
}
