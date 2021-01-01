<?php declare(strict_types = 1);

namespace PrototypeIntegration\PrototypeIntegration\Processor\PageElement;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class LanguageMenuProcessor
{
    protected ContentObjectRenderer $contentObject;

    public function __construct(ContentObjectRenderer $contentObject)
    {
        $this->contentObject = $contentObject;
    }

    /**
     *
     * @param string $languages List of language uid or auto
     * @param array $pageData
     * @param bool $currentLanguageFirst
     * @return array
     */
    public function process(string $languages, array $pageData = [], bool $currentLanguageFirst = false): array
    {
        $twigMenuData  = [];

        if (! empty($languages)) {
            $languageData = $this->getLanguageData(['languages' => $languages]);

            if (count($languageData) > 1) {
                foreach ($languageData as $key => $menuItem) {
                    $twigMenuData[$key] = $this->prepareLanguageInformation($menuItem, $pageData);
                }
            }
        }

        if ($currentLanguageFirst && count($twigMenuData) > 1) {
            $this->sortLanguages($twigMenuData);
        }

        return $twigMenuData;
    }

    /**
     * Get all available language information independently of current page
     *
     * @param array $processorConfiguration
     * @return array
     */
    protected function getLanguageData(array $processorConfiguration): array
    {
        /** @var \TYPO3\CMS\Frontend\DataProcessing\LanguageMenuProcessor $menuDataFetcher */
        $menuDataFetcher = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\DataProcessing\LanguageMenuProcessor::class);
        $menuData = $menuDataFetcher->process($this->contentObject, [], $processorConfiguration, [])['languagemenu'];
        return $menuData;
    }

    /**
     * Prepare the language information for frontend rendering. The language link depends on whether
     * page information ($pageData) exists. If no page information available, the link shows on the root page
     * otherwise to the current page.
     *
     * @param array $menuData Information about languages
     * @param array $pageData Information about the current page
     * @return array
     */
    protected function prepareLanguageInformation(array $menuData, array $pageData): array
    {
        $link = $menuData['link'];
        $pageLanguageData = $this->getPageDataForLanguage($menuData['languageId'], $pageData);

        if (!is_null($pageLanguageData)) {
            $link = $pageLanguageData['link'];

            if ($pageLanguageData['available'] === 0) {
                $link = '';
            }
        }

        $languageData = [
            'title' => $menuData['title'],
            'titleShort' => $menuData['navigationTitle'],
            'link' => $link,
            'current' => (bool)$menuData['active'],
            'sortValue' => (int)$menuData['active']
        ];

        return $languageData;
    }

    /**
     * Return the page data of given languageUid otherwise returns null
     *
     * @param int $languageUid UID of language
     * @param array $pageData Page data array
     * @return array|null
     */
    protected function getPageDataForLanguage(int $languageUid, array $pageData): ?array
    {
        $pageLanguageData = null;

        if (empty($pageData)) {
            return $pageLanguageData;
        }

        foreach ($pageData as $page) {
            if ($page['languageUid'] == $languageUid) {
                $pageLanguageData = $page;
                break;
            }
        }

        return $pageLanguageData;
    }

    /**
     * Sort the menuData descending of value sortValue. sortValue is an integer casted boolean value.
     *
     * @param array $menuData
     */
    protected function sortLanguages(array &$menuData)
    {
        $current = array_column($menuData, 'sortValue');
        array_multisort($current, SORT_DESC, $menuData);
    }
}
