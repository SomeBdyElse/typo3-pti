<?php
namespace PrototypeIntegration\PrototypeIntegration\Processor\PageElement;

/**
 * Class MetaMenuProcessor
 */
class MetaMenuProcessor
{
    /**
     *
     * @param array $typo3MenuData
     * @param array $metaNavIcon
     * @return array
     */
    public function process(array $typo3MenuData, array $metaNavIcon = []): array
    {
        $twigMenuData = $typo3MenuData;

        foreach ($twigMenuData as $key => $menuItem) {
            $twigMenuData[$key] = $this->addPageInformation($menuItem, $metaNavIcon);
        }

        return $twigMenuData;
    }

    /**
     *
     * @param array $menuData
     * @param array $metaNavIcon
     * @return array
     */
    protected function addPageInformation(array $menuData, array $metaNavIcon = []): array
    {
        $menuData['doktype'] = $menuData['data']['doktype'];
        $menuData['icon'] = '';

        $iconKey = array_search($menuData['data']['uid'], $metaNavIcon);
        if (!empty($metaNavIcon) && $iconKey !== false) {
            $menuData['icon'] = $iconKey;
        }

        return $menuData;
    }
}
