<?php declare(strict_types = 1);

namespace PrototypeIntegration\PrototypeIntegration\Processor\PageElement;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class MenuProcessor implements PtiDataProcessor
{
    protected \TYPO3\CMS\Frontend\DataProcessing\MenuProcessor $menuDataFetcher;

    protected ContentObjectRenderer $contentObjectRenderer;

    protected TypoScriptService $typoScriptService;

    public function __construct(
        \TYPO3\CMS\Frontend\DataProcessing\MenuProcessor $menuDataFetcher,
        TypoScriptService $typoScriptService
    ) {
        $this->menuDataFetcher = $menuDataFetcher;
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
        $menuConfiguration = $this->typoScriptService->convertPlainArrayToTypoScriptArray(
            $configuration['menuConfiguration']
        );

        /** @var \TYPO3\CMS\Frontend\DataProcessing\MenuProcessor $menuDataFetcher */
        $menuData = $this->menuDataFetcher->process($this->contentObjectRenderer, [], $menuConfiguration, []);
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
