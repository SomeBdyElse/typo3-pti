<?php declare(strict_types = 1);

namespace PrototypeIntegration\PrototypeIntegration\Processor\PageElement;

use PrototypeIntegration\PrototypeIntegration\Processor\TypoLinkStringProcessor;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MainMenuProcessor
{
    protected TypoScriptService $typoScriptService;

    protected TypoLinkStringProcessor $typoLinkStringProcessor;

    public function __construct(
        TypoScriptService $typoScriptService,
        TypoLinkStringProcessor $typoLinkStringProcessor
    ) {
        $this->typoScriptService = $typoScriptService;
        $this->typoLinkStringProcessor = $typoLinkStringProcessor;
    }

    /**
     *
     * @param array $typo3MenuData
     * @param string|int $doNotLinkDokType
     * @return array
     */
    public function process(array $typo3MenuData, $doNotLinkDokType = ''): array
    {
        $twigMenuData = $typo3MenuData;

        if (! empty($doNotLinkDokType)) {
            $twigMenuData = $this->unlinkDokType($twigMenuData, $doNotLinkDokType);
        }

        return $twigMenuData;
    }

    /**
     * Remove the index "data" from every menu item
     *
     * @param array $menuData
     * @return array
     */
    final public function removeDataInformation(array $menuData): array
    {
        foreach ($menuData as $key => $menuItem) {
            unset($menuData[$key]['data']);

            if (isset($menuItem['children']) && count($menuItem['children'])) {
                $menuData[$key]['children'] = $this->removeDataInformation($menuItem['children']);
            }
        }

        return $menuData;
    }

    /**
     * Remove the slug of an menu item
     *
     * @param array $menuData
     * @param string|int $unlinkDokType
     * @return array
     */
    final protected function unlinkDokType(array $menuData, $unlinkDokType): array
    {
        if (is_int($unlinkDokType)) {
            $unlinkDokType = (string)$unlinkDokType;
        }

        foreach ($menuData as $key => $menuItem) {
            if (GeneralUtility::inList($unlinkDokType, $menuItem['data']['doktype'])) {
                unset($menuData[$key]['link']);
            }

            if (isset($menuItem['children']) && count($menuItem['children'])) {
                $menuData[$key]['children'] = $this->unlinkDokType($menuItem['children'], $unlinkDokType);
            }
        }

        return $menuData;
    }
}
